<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'organization_id', 'requester_id', 'assignee_id',
        'subject', 'description', 'status', 'priority', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'tags' => 'array',
        ];
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority', $priority))
            ->when($filters['assignee'] ?? null, fn($q, $id) => $q->where('assignee_id', $id))
            ->when($filters['requester'] ?? null, fn($q, $id) => $q->where('requester_id', $id))
            ->when($filters['q'] ?? null, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $q->orderBy($sort, $filters['direction'] ?? 'desc');
            }, function ($q) {
                $q->latest();
            });
    }
}
