<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_ticket()
    {
        $customer = $this->createUser('customer');
        $response = $this->actingAsUser($customer)->postJson('/api/tickets', [
            'subject' => 'Test Ticket',
            'description' => 'Description',
        ]);
        $response->assertStatus(201);
    }

    public function test_customer_can_view_own_ticket()
    {
        $customer = $this->createUser('customer');
        $ticket = Ticket::factory()->create([
            'organization_id' => $customer->organization_id,
            'requester_id' => $customer->id,
        ]);

        $response = $this->actingAsUser($customer)->getJson("/api/tickets/{$ticket->id}");
        $response->assertStatus(200);
    }

    public function test_customer_cannot_view_other_customer_ticket()
    {
        $org = Organization::factory()->create();
        $customer = User::factory()->create(['organization_id' => $org->id, 'role' => 'customer']);
        $other = User::factory()->create(['organization_id' => $org->id, 'role' => 'customer']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $org->id,
            'requester_id' => $other->id,
        ]);

        $response = $this->actingAsUser($customer)->getJson("/api/tickets/{$ticket->id}");
        $response->assertStatus(403);
    }

    public function test_customer_cannot_update_status()
    {
        $customer = $this->createUser('customer');
        $ticket = Ticket::factory()->create([
            'organization_id' => $customer->organization_id,
            'requester_id' => $customer->id,
        ]);

        $response = $this->actingAsUser($customer)->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'open',
        ]);
    }

    public function test_admin_can_update_any_ticket_in_org()
    {
        $admin = $this->createUser('admin');
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
        ]);

        $response = $this->actingAsUser($admin)->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
        ]);
    }

    public function test_admin_can_delete_ticket()
    {
        $admin = $this->createUser('admin');
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
        ]);

        $response = $this->actingAsUser($admin)->deleteJson("/api/tickets/{$ticket->id}");
        $response->assertStatus(204);
    }

    public function test_agent_cannot_delete_ticket()
    {
        $agent = $this->createUser('agent');
        $ticket = Ticket::factory()->create([
            'organization_id' => $agent->organization_id,
        ]);

        $response = $this->actingAsUser($agent)->deleteJson("/api/tickets/{$ticket->id}");
        $response->assertStatus(403);
    }

    public function test_pagination_works()
    {
        $admin = $this->createUser('admin');
        Ticket::factory()->count(25)->create([
            'organization_id' => $admin->organization_id,
        ]);

        $response = $this->actingAsUser($admin)->getJson('/api/tickets?per_page=10');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
    }
}
