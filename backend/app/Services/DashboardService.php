<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\ActivityLog;
use App\Models\User;

class DashboardService
{
    public function getStats(User $user): array
    {
        $query = Ticket::query();

        if ($user->isCustomer()) {
            $query->where('requester_id', $user->id);
        }

        return [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'high' => (clone $query)->where('priority', 'high')->count(),
            'urgent' => (clone $query)->where('priority', 'urgent')->count(),
            'unassigned' => (clone $query)->whereNull('assignee_id')->count(),
        ];
    }

    public function getRecentActivity(User $user, int $limit = 10)
    {
        $query = ActivityLog::with(['actor', 'ticket'])->latest('created_at');

        if ($user->isCustomer()) {
            $query->whereHas('ticket', function ($q) use ($user) {
                $q->where('requester_id', $user->id);
            });
        } else {
            $query->whereHas('ticket', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        return $query->limit($limit)->get();
    }
}
