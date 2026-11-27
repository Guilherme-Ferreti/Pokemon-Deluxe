<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MissionGoal: string
{
    case CATCH_POKEMON          = 'catch-pokemon';
    case CATCH_POKEMON_OF_TYPE  = 'catch-pokemon-of-type';
    case CATCH_POKEMON_IN_AREA  = 'catch-pokemon-in-area';
    case CATCH_SHINY            = 'catch-shiny';
    case CATCH_LEGENDARY        = 'catch-legendary';
    case CATCH_POKEMON_AT_LEVEL = 'catch-pokemon-at-level';
    case WIN_BATTLES            = 'win-battles';
    case WIN_WILD_BATTLES       = 'win-wild-battles';
    case WIN_TRAINER_BATTLES    = 'win-trainer-battles';
    case BEAT_GYM               = 'beat-gym';
    case EARN_REGION_BADGES     = 'earn-region-badges';
    case BECOME_CHAMPION        = 'become-champion';
    case CLIMB_TOWER_FLOORS     = 'climb-tower-floors';
    case HATCH_EGGS             = 'hatch-eggs';
    case EVOLVE_POKEMON         = 'evolve-pokemon';
    case MEGA_EVOLVE            = 'mega-evolve';
    case REACH_LEVEL            = 'reach-level';
    case REACH_BOOST            = 'reach-boost';
    case TEACH_TMS              = 'teach-tms';
    case SPEND_MONEY            = 'spend-money';
    case MAKE_TRADES            = 'make-trades';

    public function label(): string
    {
        return match ($this) {
            self::CATCH_POKEMON          => 'Catch Pokémon',
            self::CATCH_POKEMON_OF_TYPE  => 'Catch Pokémon of a type',
            self::CATCH_POKEMON_IN_AREA  => 'Catch Pokémon in an area',
            self::CATCH_SHINY            => 'Catch a shiny Pokémon',
            self::CATCH_LEGENDARY        => 'Catch a legendary or mythical Pokémon',
            self::CATCH_POKEMON_AT_LEVEL => 'Catch a Pokémon at a level',
            self::WIN_BATTLES            => 'Win battles',
            self::WIN_WILD_BATTLES       => 'Win wild battles',
            self::WIN_TRAINER_BATTLES    => 'Win battles against trainers',
            self::BEAT_GYM               => 'Beat gyms',
            self::EARN_REGION_BADGES     => 'Earn the badges of a region',
            self::BECOME_CHAMPION        => 'Become Champion',
            self::CLIMB_TOWER_FLOORS     => 'Climb Battle Tower floors',
            self::HATCH_EGGS             => 'Hatch eggs',
            self::EVOLVE_POKEMON         => 'Evolve Pokémon',
            self::MEGA_EVOLVE            => 'Mega evolve a Pokémon',
            self::REACH_LEVEL            => 'Reach a level',
            self::REACH_BOOST            => 'Reach a boost',
            self::TEACH_TMS              => 'Teach TMs',
            self::SPEND_MONEY            => 'Spend money',
            self::MAKE_TRADES            => 'Make trades',
        };
    }
}
