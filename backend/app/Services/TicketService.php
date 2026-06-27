<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class TicketService
{
    public function create(array $data, User $user): Ticket
    {
        $ticket = new Ticket([
            'organization_id' => $user->organization_id,
            'requester_id' => $data['requester_id'] ?? $user->id,
            'subject' => $data['subject'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => TicketStatus::OPEN,
            'tags' => $data['tags'] ?? null,
        ]);
        $ticket->save();

        ActivityLogService::log($ticket, ActivityAction::CREATED->value, $user);

        return $ticket->fresh();
    }

    public function update(Ticket $ticket, array $data, User $user): Ticket
    {
        $oldStatus = $ticket->status->value;
        $oldPriority = $ticket->priority->value;
        $oldAssignee = $ticket->assignee_id;

        $ticket->update($data);

        if ($ticket->wasChanged('status')) {
            ActivityLogService::log($ticket, ActivityAction::STATUS_CHANGED->value, $user, [
                'old' => $oldStatus,
                'new' => $ticket->status->value,
            ]);
        }

        if ($ticket->wasChanged('priority')) {
            ActivityLogService::log($ticket, ActivityAction::PRIORITY_CHANGED->value, $user, [
                'old' => $oldPriority,
                'new' => $ticket->priority->value,
            ]);
        }

        if ($ticket->wasChanged('assignee_id')) {
            ActivityLogService::log($ticket, ActivityAction::ASSIGNED->value, $user, [
                'old' => $oldAssignee,
                'new' => $ticket->assignee_id,
            ]);
        }

        return $ticket->fresh();
    }

    public function assign(Ticket $ticket, ?int $assigneeId, User $user): Ticket
    {
        $old = $ticket->assignee_id;
        $ticket->update(['assignee_id' => $assigneeId]);

        ActivityLogService::log($ticket, ActivityAction::ASSIGNED->value, $user, [
            'old' => $old,
            'new' => $assigneeId,
        ]);

        return $ticket->fresh();
    }

    public function getTicketsForUser(User $user, array $filters)
    {
        $query = Ticket::query()->with(['requester', 'assignee'])->filter($filters);

        if ($user->isCustomer()) {
            $query->where('requester_id', $user->id);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
