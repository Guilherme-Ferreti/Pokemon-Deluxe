<?php

declare(strict_types=1);

namespace App\Pokemon\Enums;

enum PokemonTrait: string
{
    case SHINY     = 'shiny';
    case BABY      = 'baby';
    case LEGENDARY = 'legendary';
    case MYTHICAL  = 'mythical';
    case MEGA      = 'mega';
    case GMAX      = 'gmax';

    public function label(): string
    {
        return match ($this) {
            self::SHINY     => 'Shiny',
            self::BABY      => 'Baby',
            self::LEGENDARY => 'Legendary',
            self::MYTHICAL  => 'Mythical',
            self::MEGA      => 'Mega',
            self::GMAX      => 'G-Max',
        };
    }
}
