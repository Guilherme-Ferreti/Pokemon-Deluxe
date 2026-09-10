<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MoveDamageClass: string
{
    case PHYSICAL = 'physical';
    case SPECIAL  = 'special';
    case STATUS   = 'status';

    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL => 'Physical',
            self::SPECIAL  => 'Special',
            self::STATUS   => 'Status',
        };
    }
}
