<?php

declare(strict_types=1);

namespace App\Pokemon\Mechanics;

use Shared\ValueObjects\PokemonStats;

final class StatsCalculator
{
    public function calculate(int $level, int $boost, bool $isShiny): PokemonStats
    {
        $levelBonus     = (int) config('game.pokemon.stats.level_bonus');
        $levelFactor    = $level + $levelBonus;
        $maxLevelFactor = (int) config('game.pokemon.max_level') + $levelBonus;
        $boostFactor    = 1 + $boost / 100;

        $hp = (int) config('game.pokemon.stats.max_level_hp') * $levelFactor / $maxLevelFactor * $boostFactor;

        $attack = $levelFactor / (int) config('game.pokemon.stats.damage_scale') * $boostFactor;

        $defense = 1.0;

        if ($isShiny) {
            $hp *= (float) config('game.pokemon.stats.shiny_hp_multiplier');
            $attack *= (float) config('game.pokemon.stats.shiny_damage_done_multiplier');
            $defense *= (float) config('game.pokemon.stats.shiny_damage_taken_multiplier');
        }

        return new PokemonStats(
            hp: (int) round($hp),
            attack: $attack,
            defense: $defense,
        );
    }
}
