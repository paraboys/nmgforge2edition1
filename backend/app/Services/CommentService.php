<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentService
{
    public function create(Ticket $ticket, array $data, User $user): Comment
    {
        $isInternal = false;
        if ($user->isAdmin() || $user->isAgent()) {
            $isInternal = $data['is_internal'] ?? false;
        }

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'author_id' => $user->id,
            'body' => $data['body'],
            'is_internal' => $isInternal,
        ]);

        $action = $isInternal ? ActivityAction::INTERNAL_NOTE->value : ActivityAction::REPLIED->value;
        ActivityLogService::log($ticket, $action, $user);

        return $comment;
    }
}
