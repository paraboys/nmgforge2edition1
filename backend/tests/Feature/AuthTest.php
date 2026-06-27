<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_201_with_token_and_user()
    {
        $response = $this->postJson('/api/register', [
            'organization_name' => 'Test Org',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_returns_200_with_token()
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_with_wrong_password_returns_401()
    {
        $org = Organization::factory()->create();
        User::factory()->create([
            'organization_id' => $org->id,
            'email' => 'wrong@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_logout_returns_200()
    {
        $user = $this->createUser('admin');
        $response = $this->actingAsUser($user)->postJson('/api/logout');
        $response->assertStatus(200);
    }

    public function test_me_returns_user_data()
    {
        $user = $this->createUser('admin');
        $response = $this->actingAsUser($user)->getJson('/api/me');
        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_me_without_token_returns_401()
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }
}
