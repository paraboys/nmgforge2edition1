<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_users_in_org()
    {
        $admin = $this->createUser('admin');
        User::factory()->count(3)->create(['organization_id' => $admin->organization_id]);

        $response = $this->actingAsUser($admin)->getJson('/api/users');
        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_agent_lists_only_customers()
    {
        $org = \App\Models\Organization::factory()->create();
        $agent = User::factory()->create(['organization_id' => $org->id, 'role' => 'agent']);
        User::factory()->count(2)->create(['organization_id' => $org->id, 'role' => 'customer']);
        User::factory()->create(['organization_id' => $org->id, 'role' => 'admin']);

        $response = $this->actingAsUser($agent)->getJson('/api/users');
        $response->assertStatus(200);
        foreach ($response->json('data') as $user) {
            $this->assertEquals('customer', $user['role']);
        }
    }

    public function test_customer_lists_only_self()
    {
        $org = \App\Models\Organization::factory()->create();
        $customer = User::factory()->create(['organization_id' => $org->id, 'role' => 'customer']);
        User::factory()->create(['organization_id' => $org->id, 'role' => 'customer']);

        $response = $this->actingAsUser($customer)->getJson('/api/users');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($customer->id, $response->json('data')[0]['id']);
    }

    public function test_admin_can_create_user()
    {
        $admin = $this->createUser('admin');
        $response = $this->actingAsUser($admin)->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'agent',
        ]);
        $response->assertStatus(201);
    }

    public function test_agent_cannot_create_user()
    {
        $agent = $this->createUser('agent');
        $response = $this->actingAsUser($agent)->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'agent',
        ]);
        $response->assertStatus(403);
    }
}
