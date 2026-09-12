<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MoveTrait: string
{
    case Z_MOVE  = 'z-move';
    case DYNAMAX = 'dynamax';

    public function label(): string
    {
        return match ($this) {
            self::Z_MOVE  => 'Z-Move',
            self::DYNAMAX => 'Dynamax',
        };
    }
}
