<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_post_public_comment()
    {
        $customer = $this->createUser('customer');
        $ticket = Ticket::factory()->create([
            'organization_id' => $customer->organization_id,
            'requester_id' => $customer->id,
        ]);

        $response = $this->actingAsUser($customer)->postJson("/api/tickets/{$ticket->id}/comments", [
            'body' => 'Public reply',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', ['body' => 'Public reply', 'is_internal' => false]);
    }

    public function test_customer_cannot_post_internal_comment()
    {
        $customer = $this->createUser('customer');
        $ticket = Ticket::factory()->create([
            'organization_id' => $customer->organization_id,
            'requester_id' => $customer->id,
        ]);

        $response = $this->actingAsUser($customer)->postJson("/api/tickets/{$ticket->id}/comments", [
            'body' => 'Reply',
            'is_internal' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', ['body' => 'Reply', 'is_internal' => false]);
    }

    public function test_customer_cannot_see_internal_comments()
    {
        $org = \App\Models\Organization::factory()->create();
        $customer = User::factory()->create(['organization_id' => $org->id, 'role' => 'customer']);
        $agent = User::factory()->create(['organization_id' => $org->id, 'role' => 'agent']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $org->id,
            'requester_id' => $customer->id,
        ]);

        Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'author_id' => $agent->id,
            'is_internal' => true,
            'body' => 'Secret note',
        ]);

        $response = $this->actingAsUser($customer)->getJson("/api/tickets/{$ticket->id}/comments");
        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    public function test_comment_author_can_delete_within_15_minutes()
    {
        $customer = $this->createUser('customer');
        $ticket = Ticket::factory()->create([
            'organization_id' => $customer->organization_id,
            'requester_id' => $customer->id,
        ]);

        $comment = Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'author_id' => $customer->id,
        ]);

        $response = $this->actingAsUser($customer)->deleteJson("/api/comments/{$comment->id}");
        $response->assertStatus(204);
    }
}
