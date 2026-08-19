<?php

declare(strict_types=1);

namespace Shared\Enums;

enum SpeciesTrait: string
{
    case BABY      = 'baby';
    case LEGENDARY = 'legendary';
    case MYTHICAL  = 'mythical';
    case MEGA      = 'mega';
    case GMAX      = 'gmax';

    public function label(): string
    {
        return match ($this) {
            self::BABY      => 'Baby',
            self::LEGENDARY => 'Legendary',
            self::MYTHICAL  => 'Mythical',
            self::MEGA      => 'Mega',
            self::GMAX      => 'G-Max',
        };
    }
}
