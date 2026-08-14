<?php

declare(strict_types=1);

namespace Shared\Enums;

enum Generation: int
{
    case I    = 1;
    case II   = 2;
    case III  = 3;
    case IV   = 4;
    case V    = 5;
    case VI   = 6;
    case VII  = 7;
    case VIII = 8;
    case IX   = 9;

    public function label(): string
    {
        return match ($this) {
            self::I    => 'Gen I',
            self::II   => 'Gen II',
            self::III  => 'Gen III',
            self::IV   => 'Gen IV',
            self::V    => 'Gen V',
            self::VI   => 'Gen VI',
            self::VII  => 'Gen VII',
            self::VIII => 'Gen VIII',
            self::IX   => 'Gen IX',
        };
    }

    public function region(): string
    {
        return match ($this) {
            self::I    => 'Kanto',
            self::II   => 'Johto',
            self::III  => 'Hoenn',
            self::IV   => 'Sinnoh',
            self::V    => 'Unova',
            self::VI   => 'Kalos',
            self::VII  => 'Alola',
            self::VIII => 'Galar',
            self::IX   => 'Paldea',
        };
    }
}
