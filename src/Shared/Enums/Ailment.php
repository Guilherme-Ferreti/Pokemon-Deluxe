<?php

declare(strict_types=1);

namespace Shared\Enums;

enum Ailment: string
{
    case BURN      = 'burn';
    case CONFUSION = 'confusion';
    case FREEZE    = 'freeze';
    case PARALYSIS = 'paralysis';
    case POISON    = 'poison';
    case SLEEP     = 'sleep';
    case UNKNOWN   = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::BURN      => 'Burn',
            self::CONFUSION => 'Confusion',
            self::FREEZE    => 'Freeze',
            self::PARALYSIS => 'Paralysis',
            self::POISON    => 'Poison',
            self::SLEEP     => 'Sleep',
            self::UNKNOWN   => 'Unknown',
        };
    }
}
