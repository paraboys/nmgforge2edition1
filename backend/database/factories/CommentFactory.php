<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'author_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_internal' => false,
        ];
    }
}
