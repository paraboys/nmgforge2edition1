<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return $user->organization_id === $ticket->organization_id;
        }
        return $user->id === $ticket->requester_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return $user->organization_id === $ticket->organization_id;
        }
        return $user->id === $ticket->requester_id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() && $user->organization_id === $ticket->organization_id;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return ($user->isAdmin() || $user->isAgent()) && $user->organization_id === $ticket->organization_id;
    }
}
