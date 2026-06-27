<?php

namespace App\Enums;

enum ActivityAction: string
{
    case CREATED = 'created';
    case ASSIGNED = 'assigned';
    case STATUS_CHANGED = 'status_changed';
    case PRIORITY_CHANGED = 'priority_changed';
    case REPLIED = 'replied';
    case INTERNAL_NOTE = 'internal_note';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::ASSIGNED => 'Assigned',
            self::STATUS_CHANGED => 'Status Changed',
            self::PRIORITY_CHANGED => 'Priority Changed',
            self::REPLIED => 'Replied',
            self::INTERNAL_NOTE => 'Internal Note',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
