<?php

declare(strict_types=1);

namespace Shared\Enums;

use LogicException;

enum Area: string
{
    case MEADOW        = 'meadow';
    case FOREST        = 'forest';
    case COAST         = 'coast';
    case VOLCANO       = 'volcano';
    case POWER_PLANT   = 'power-plant';
    case CAVE          = 'cave';
    case SNOWY_PEAK    = 'snowy-peak';
    case HAUNTED_TOWER = 'haunted-tower';
    case ANCIENT_RUINS = 'ancient-ruins';

    public function label(): string
    {
        return match ($this) {
            self::MEADOW        => 'Meadow',
            self::FOREST        => 'Forest',
            self::COAST         => 'Coast',
            self::VOLCANO       => 'Volcano',
            self::POWER_PLANT   => 'Power Plant',
            self::CAVE          => 'Cave',
            self::SNOWY_PEAK    => 'Snowy Peak',
            self::HAUNTED_TOWER => 'Haunted Tower',
            self::ANCIENT_RUINS => 'Ancient Ruins',
        };
    }

    /**
     * @return array{PokemonType, PokemonType}
     */
    public function types(): array
    {
        return match ($this) {
            self::MEADOW        => [PokemonType::NORMAL, PokemonType::FAIRY],
            self::FOREST        => [PokemonType::GRASS, PokemonType::BUG],
            self::COAST         => [PokemonType::WATER, PokemonType::FLYING],
            self::VOLCANO       => [PokemonType::FIRE, PokemonType::GROUND],
            self::POWER_PLANT   => [PokemonType::ELECTRIC, PokemonType::STEEL],
            self::CAVE          => [PokemonType::ROCK, PokemonType::FIGHTING],
            self::SNOWY_PEAK    => [PokemonType::ICE, PokemonType::DRAGON],
            self::HAUNTED_TOWER => [PokemonType::GHOST, PokemonType::DARK],
            self::ANCIENT_RUINS => [PokemonType::PSYCHIC, PokemonType::POISON],
        };
    }

    public static function fromType(PokemonType $type): self
    {
        return array_find(self::cases(), fn (self $area) => in_array($type, $area->types(), true))
            ?? throw new LogicException("No area has the [{$type->value}] type.");
    }
}
