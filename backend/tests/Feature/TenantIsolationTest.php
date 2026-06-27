<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_other_org_tickets()
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $userA = User::factory()->create(['organization_id' => $orgA->id]);
        $userB = User::factory()->create(['organization_id' => $orgB->id]);

        $ticketA = Ticket::factory()->create([
            'organization_id' => $orgA->id,
            'requester_id' => $userA->id,
        ]);

        Ticket::factory()->create([
            'organization_id' => $orgB->id,
            'requester_id' => $userB->id,
        ]);

        $response = $this->actingAsUser($userA)->getJson('/api/tickets');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($ticketA->id, $data[0]['id']);
    }

    public function test_user_cannot_access_other_org_ticket_by_id()
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $userA = User::factory()->create(['organization_id' => $orgA->id]);
        $userB = User::factory()->create(['organization_id' => $orgB->id]);

        $ticketB = Ticket::factory()->create([
            'organization_id' => $orgB->id,
            'requester_id' => $userB->id,
        ]);

        $response = $this->actingAsUser($userA)->getJson("/api/tickets/{$ticketB->id}");
        $response->assertStatus(403);
    }
}
