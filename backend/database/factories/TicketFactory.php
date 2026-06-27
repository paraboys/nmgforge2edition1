<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'requester_id' => User::factory(),
            'assignee_id' => null,
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(TicketStatus::values()),
            'priority' => fake()->randomElement(TicketPriority::values()),
            'tags' => null,
        ];
    }
}
