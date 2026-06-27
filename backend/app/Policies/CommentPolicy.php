<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return $user->organization_id === $ticket->organization_id;
        }
        return $user->id === $ticket->requester_id;
    }

    public function create(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->id === $comment->author_id) {
            return $comment->created_at->diffInMinutes(now()) <= 15;
        }
        return false;
    }
}
