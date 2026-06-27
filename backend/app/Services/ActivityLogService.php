<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\User;

class ActivityLogService
{
    public static function log(Ticket $ticket, string $action, ?User $user = null, array $meta = []): ActivityLog
    {
        return ActivityLog::create([
            'ticket_id' => $ticket->id,
            'actor_id' => $user?->id,
            'action' => $action,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }
}
