<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_isolates_tickets_between_organizations(): void
    {
        $org1 = Organization::create(['name' => 'Org 1']);
        $org2 = Organization::create(['name' => 'Org 2']);

        $user1 = User::factory()->create([
            'organization_id' => $org1->id,
            'role' => 'admin',
        ]);

        $user2 = User::factory()->create([
            'organization_id' => $org2->id,
            'role' => 'admin',
        ]);

        // Create ticket for Org 1
        Sanctum::actingAs($user1);
        
        $response1 = $this->postJson('/api/tickets', [
            'subject' => 'Org 1 Ticket',
            'description' => 'Test description',
            'priority' => 'high',
        ]);
        
        $response1->assertStatus(201);
        
        // Create ticket for Org 2
        Sanctum::actingAs($user2);
        
        $response2 = $this->postJson('/api/tickets', [
            'subject' => 'Org 2 Ticket',
            'description' => 'Test description',
            'priority' => 'low',
        ]);
        
        $response2->assertStatus(201);

        // Assert Org 1 can only see their ticket
        Sanctum::actingAs($user1);
        $this->getJson('/api/tickets')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject', 'Org 1 Ticket');
            
        // Assert Org 2 can only see their ticket
        Sanctum::actingAs($user2);
        $this->getJson('/api/tickets')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject', 'Org 2 Ticket');
    }
}
