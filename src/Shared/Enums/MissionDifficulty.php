<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MissionDifficulty: string
{
    case EASY   = 'easy';
    case MEDIUM = 'medium';
    case HARD   = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::EASY   => 'Easy',
            self::MEDIUM => 'Medium',
            self::HARD   => 'Hard',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::EASY   => 1,
            self::MEDIUM => 2,
            self::HARD   => 3,
        };
    }
}
