<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => ['role' => UserRole::ADMIN]);
    }

    public function agent(): static
    {
        return $this->state(fn(array $attributes) => ['role' => UserRole::AGENT]);
    }
}
