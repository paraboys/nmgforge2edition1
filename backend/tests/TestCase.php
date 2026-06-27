<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function createUser(string $role, ?Organization $org = null): User
    {
        $org = $org ?? Organization::factory()->create();
        return User::factory()->create([
            'organization_id' => $org->id,
            'role' => $role,
        ]);
    }

    protected function actingAsUser(User $user): self
    {
        Sanctum::actingAs($user, ['*']);
        return $this;
    }
}
