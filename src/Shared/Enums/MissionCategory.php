<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MissionCategory: string
{
    case CATCHING   = 'catching';
    case BATTLING   = 'battling';
    case COLLECTION = 'collection';

    public function label(): string
    {
        return match ($this) {
            self::CATCHING   => 'Catching',
            self::BATTLING   => 'Battling',
            self::COLLECTION => 'Collection',
        };
    }
}
