<?php

declare(strict_types=1);

namespace Shared\Enums;

use ValueError;

enum Generation: int
{
    case KANTO  = 1;
    case JOHTO  = 2;
    case HOENN  = 3;
    case SINNOH = 4;
    case UNOVA  = 5;
    case KALOS  = 6;
    case ALOLA  = 7;
    case GALAR  = 8;
    case PALDEA = 9;

    public static function fromString(string $value): self
    {
        $value = strtolower($value);

        return match ($value) {
            'kanto'  => self::KANTO,
            'johto'  => self::JOHTO,
            'hoenn'  => self::HOENN,
            'sinnoh' => self::SINNOH,
            'unova'  => self::UNOVA,
            'kalos'  => self::KALOS,
            'alola'  => self::ALOLA,
            'galar'  => self::GALAR,
            'paldea' => self::PALDEA,
            default  => throw new ValueError('Unknown generation: ' . $value),
        };
    }
}
