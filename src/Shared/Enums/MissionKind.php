<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MissionKind: string
{
    case DAILY        = 'daily';
    case ACHIEVEMENT  = 'achievement';
    case STREAK_BONUS = 'streak-bonus';

    public function label(): string
    {
        return match ($this) {
            self::DAILY        => 'Daily',
            self::ACHIEVEMENT  => 'Achievement',
            self::STREAK_BONUS => 'Streak bonus',
        };
    }
}
