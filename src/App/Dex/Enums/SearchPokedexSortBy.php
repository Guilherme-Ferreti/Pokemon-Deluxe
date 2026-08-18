<?php

declare(strict_types=1);

namespace App\Dex\Enums;

enum SearchPokedexSortBy: string
{
    case POKEDEX_NUMBER = 'pokedex-number';
    case NAME           = 'name';
}
