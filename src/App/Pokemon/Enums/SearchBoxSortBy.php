<?php

declare(strict_types=1);

namespace App\Pokemon\Enums;

enum SearchBoxSortBy: string
{
    case CAPTURE_DATE = 'capture-date';
    case EXPERIENCE   = 'experience';
    case LEVEL        = 'level';

    public function label(): string
    {
        return match ($this) {
            self::CAPTURE_DATE => 'Capture Date',
            self::EXPERIENCE   => 'Experience',
            self::LEVEL        => 'Level',
        };
    }
}
