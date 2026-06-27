<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ActivityLog::truncate();
        Comment::truncate();
        Ticket::truncate();
        User::truncate();
        Organization::truncate();
        Schema::enableForeignKeyConstraints();

        $org = Organization::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

        $admin = User::create([
            'organization_id' => $org->id,
            'name' => 'Admin User',
            'email' => 'admin@acme.test',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        $agent = User::create([
            'organization_id' => $org->id,
            'name' => 'Agent User',
            'email' => 'agent@acme.test',
            'password' => Hash::make('password'),
            'role' => UserRole::AGENT,
        ]);

        $customer = User::create([
            'organization_id' => $org->id,
            'name' => 'Customer User',
            'email' => 'customer@acme.test',
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
        ]);

        $statuses = [TicketStatus::OPEN, TicketStatus::PENDING, TicketStatus::RESOLVED, TicketStatus::CLOSED];
        $priorities = [TicketPriority::LOW, TicketPriority::MEDIUM, TicketPriority::HIGH, TicketPriority::URGENT];

        for ($i = 0; $i < 12; $i++) {
            $ticket = Ticket::create([
                'organization_id' => $org->id,
                'requester_id' => $customer->id,
                'assignee_id' => ($i % 3 === 0) ? null : $agent->id,
                'subject' => 'Demo Ticket #' . ($i + 1),
                'description' => 'This is a demo ticket description for ticket #' . ($i + 1) . '.',
                'status' => $statuses[$i % 4],
                'priority' => $priorities[$i % 4],
                'tags' => ['demo', 'test'],
            ]);

            for ($j = 0; $j < 2; $j++) {
                Comment::create([
                    'ticket_id' => $ticket->id,
                    'author_id' => ($j === 0) ? $customer->id : $agent->id,
                    'body' => 'Reply #' . ($j + 1) . ' on ticket #' . ($i + 1),
                    'is_internal' => ($j === 1 && $i % 2 === 0),
                ]);
            }
        }
    }
}
