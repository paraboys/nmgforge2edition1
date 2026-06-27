<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_stats_for_admin()
    {
        $admin = $this->createUser('admin');
        Ticket::factory()->count(5)->create([
            'organization_id' => $admin->organization_id,
            'status' => 'open',
        ]);

        $response = $this->actingAsUser($admin)->getJson('/api/dashboard');
        $response->assertStatus(200)
            ->assertJsonPath('stats.total', 5);
    }

    public function test_dashboard_returns_customer_scoped_stats()
    {
        $customer = $this->createUser('customer');
        Ticket::factory()->count(3)->create([
            'organization_id' => $customer->organization_id,
            'requester_id' => $customer->id,
        ]);
        Ticket::factory()->create([
            'organization_id' => $customer->organization_id,
        ]);

        $response = $this->actingAsUser($customer)->getJson('/api/dashboard');
        $response->assertStatus(200)
            ->assertJsonPath('stats.total', 3);
    }
}
