<?php

declare(strict_types=1);

namespace Shared\Enums;

enum PokemonCharacteristic: string
{
    case SHINY     = 'shiny';
    case BABY      = 'baby';
    case LEGENDARY = 'legendary';
    case MYTHICAL  = 'mythical';
    case MEGA      = 'mega';
    case GMAX      = 'gmax';
}
