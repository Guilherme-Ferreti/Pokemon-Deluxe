<?php

declare(strict_types=1);

namespace Shared\Enums;

enum PokemonGender: int
{
    case GENDERLESS = 0;
    case MALE       = 1;
    case FEMALE     = 2;
}
