<?php

declare(strict_types=1);

namespace Shared\Enums;

enum PokemonGenderRate: int
{
    case GENDERLESS    = -1;
    case ONLY_MALE     = 0;
    case RARELY_FEMALE = 1;
    case USUALY_MALE   = 2;
    case EQUAL         = 4;
    case USUALY_FEMALE = 6;
    case RARELY_MALE   = 7;
    case ONLY_FEMALE   = 8;

    public function malePercentage(): int|float
    {
        return match ($this) {
            self::GENDERLESS    => 0,
            self::ONLY_MALE     => 100,
            self::RARELY_FEMALE => 87.5,
            self::USUALY_MALE   => 75,
            self::EQUAL         => 50,
            self::USUALY_FEMALE => 25,
            self::RARELY_MALE   => 12.5,
            self::ONLY_FEMALE   => 0,
        };
    }

    public function femalePercentage(): int|float
    {
        return match ($this) {
            self::GENDERLESS    => 0,
            self::ONLY_MALE     => 0,
            self::RARELY_FEMALE => 12.5,
            self::USUALY_MALE   => 25,
            self::EQUAL         => 50,
            self::USUALY_FEMALE => 75,
            self::RARELY_MALE   => 87.5,
            self::ONLY_FEMALE   => 100,
        };
    }
}
