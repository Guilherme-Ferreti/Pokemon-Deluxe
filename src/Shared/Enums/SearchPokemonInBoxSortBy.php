<?php

declare(strict_types=1);

namespace Shared\Enums;

enum SearchPokemonInBoxSortBy: string
{
    case CAPTURE_DATE = 'capture-date';
    case EXPERIENCE   = 'experience';
    case LEVEL        = 'level';
}
