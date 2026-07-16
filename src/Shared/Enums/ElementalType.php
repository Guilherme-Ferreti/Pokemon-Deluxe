<?php

declare(strict_types=1);

namespace Shared\Enums;

enum ElementalType: string
{
    case BUG      = 'bug';
    case DARK     = 'dark';
    case DRAGON   = 'dragon';
    case ELECTRIC = 'electric';
    case FAIRY    = 'fairy';
    case FIGHTING = 'fighting';
    case FIRE     = 'fire';
    case FLYING   = 'flying';
    case GHOST    = 'ghost';
    case GRASS    = 'grass';
    case GROUND   = 'ground';
    case ICE      = 'ice';
    case NORMAL   = 'normal';
    case POISON   = 'poison';
    case PSYCHIC  = 'psychic';
    case ROCK     = 'rock';
    case STEEL    = 'steel';
    case WATER    = 'water';

    public function label(): string
    {
        return match ($this) {
            self::BUG      => 'Bug',
            self::DARK     => 'Dark',
            self::DRAGON   => 'Dragon',
            self::ELECTRIC => 'Electric',
            self::FAIRY    => 'Fairy',
            self::FIGHTING => 'Fighting',
            self::FIRE     => 'Fire',
            self::FLYING   => 'Flying',
            self::GHOST    => 'Ghost',
            self::GRASS    => 'Grass',
            self::GROUND   => 'Ground',
            self::ICE      => 'Ice',
            self::NORMAL   => 'Normal',
            self::POISON   => 'Poison',
            self::PSYCHIC  => 'Psychic',
            self::ROCK     => 'Rock',
            self::STEEL    => 'Steel',
            self::WATER    => 'Water',
        };
    }
}
