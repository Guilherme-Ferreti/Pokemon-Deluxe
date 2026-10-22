<?php

declare(strict_types=1);

namespace App\Pokemon\Enums;

enum SearchBoxSortBy: string
{
    case CATCH_DATE     = 'catch-date';
    case EXPERIENCE     = 'experience';
    case LEVEL          = 'level';
    case POKEDEX_NUMBER = 'pokedex-number';

    public function label(): string
    {
        return match ($this) {
            self::CATCH_DATE     => 'Catch date',
            self::EXPERIENCE     => 'Experience',
            self::LEVEL          => 'Level',
            self::POKEDEX_NUMBER => 'Pokedex number',
        };
    }
}
