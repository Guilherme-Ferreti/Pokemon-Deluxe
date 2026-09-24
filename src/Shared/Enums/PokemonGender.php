<?php

declare(strict_types=1);

namespace Shared\Enums;

enum PokemonGender: string
{
    case GENDERLESS = 'genderless';
    case MALE       = 'male';
    case FEMALE     = 'female';

    public function label(): string
    {
        return match ($this) {
            self::GENDERLESS => 'Genderless',
            self::MALE       => 'Male',
            self::FEMALE     => 'Female',
        };
    }
}
