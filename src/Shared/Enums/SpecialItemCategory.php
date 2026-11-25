<?php

declare(strict_types=1);

namespace Shared\Enums;

enum SpecialItemCategory: string
{
    case BOOST_STONE = 'boost-stone';
    case KEY_ITEM    = 'key-item';

    public function label(): string
    {
        return match ($this) {
            self::BOOST_STONE => 'Boost stone',
            self::KEY_ITEM    => 'Key item',
        };
    }
}
