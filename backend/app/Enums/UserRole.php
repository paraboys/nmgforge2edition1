<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::AGENT => 'Agent',
            self::CUSTOMER => 'Customer',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
