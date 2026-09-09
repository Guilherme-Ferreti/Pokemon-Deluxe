<?php

declare(strict_types=1);

namespace Shared\Enums;

enum PokemonGender: int
{
    case GENDERLESS = 0;
    case MALE       = 1;
    case FEMALE     = 2;

    public function label(): string
    {
        return match ($this) {
            self::GENDERLESS => 'Genderless',
            self::MALE       => 'Male',
            self::FEMALE     => 'Female',
        };
    }
}
