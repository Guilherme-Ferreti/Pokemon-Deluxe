<?php

declare(strict_types=1);

namespace App\Battle\Enums;

enum Side: string
{
    case PLAYER   = 'player';
    case OPPONENT = 'opponent';

    public function other(): self
    {
        return match ($this) {
            self::PLAYER   => self::OPPONENT,
            self::OPPONENT => self::PLAYER,
        };
    }
}
