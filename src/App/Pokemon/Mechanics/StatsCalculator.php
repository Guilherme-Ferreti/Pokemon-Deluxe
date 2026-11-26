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

        $hp = (int) config('game.pokemon.stats.max_level_hp') * $levelFactor / $maxLevelFactor * $this->hpMultiplier($boost, $isShiny);

        $attack = $levelFactor / (int) config('game.pokemon.stats.damage_scale') * $this->damageDoneMultiplier($boost, $isShiny);

        return new PokemonStats(
            hp: (int) round($hp),
            attack: $attack,
            defense: $this->damageTakenMultiplier($boost, $isShiny),
        );
    }

    public function hpMultiplier(int $boost, bool $isShiny): float
    {
        $multiplier = $this->boostMultiplier($boost);

        return $isShiny ? $multiplier * (float) config('game.pokemon.stats.shiny_hp_multiplier') : $multiplier;
    }

    public function damageDoneMultiplier(int $boost, bool $isShiny): float
    {
        $multiplier = $this->boostMultiplier($boost);

        return $isShiny ? $multiplier * (float) config('game.pokemon.stats.shiny_damage_done_multiplier') : $multiplier;
    }

    public function damageTakenMultiplier(int $boost, bool $isShiny): float
    {
        $multiplier = 1 / $this->boostMultiplier($boost);

        return $isShiny ? $multiplier * (float) config('game.pokemon.stats.shiny_damage_taken_multiplier') : $multiplier;
    }

    private function boostMultiplier(int $boost): float
    {
        return 1 + $boost / 100;
    }

    public function hpBonusPercentage(int $boost, bool $isShiny): float
    {
        return ($this->hpMultiplier($boost, $isShiny) - 1) * 100;
    }

    public function damageDoneBonusPercentage(int $boost, bool $isShiny): float
    {
        return ($this->damageDoneMultiplier($boost, $isShiny) - 1) * 100;
    }

    public function damageTakenChangePercentage(int $boost, bool $isShiny): float
    {
        return ($this->damageTakenMultiplier($boost, $isShiny) - 1) * 100;
    }
}
