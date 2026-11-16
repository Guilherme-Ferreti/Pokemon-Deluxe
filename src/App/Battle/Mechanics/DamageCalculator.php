<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Enums\PokemonType;
use Shared\Models\Move;
use Shared\ValueObjects\PokemonStats;

final class DamageCalculator
{
    /**
     * @param  array<int, PokemonType>  $attackerTypes
     * @param  array<int, PokemonType>  $defenderTypes
     */
    public function calculate(
        PokemonStats $attackerStats,
        array $attackerTypes,
        PokemonStats $defenderStats,
        array $defenderTypes,
        Move $move,
        bool $isCriticalHit,
        float $randomFactor,
        bool $isAttackerBurned,
    ): int {
        if ($move->power === null) {
            return 0;
        }

        $typeMultiplier = $this->typeMultiplier($move->type, ...$defenderTypes);

        if ($typeMultiplier === 0.0) {
            return 0;
        }

        $damage = $move->power
            * $attackerStats->attack
            * $this->stabMultiplier($move->type, $attackerTypes)
            * $typeMultiplier
            * $this->criticalHitMultiplier($isCriticalHit)
            * $randomFactor
            * $this->burnMultiplier($isAttackerBurned)
            * $defenderStats->defense;

        return max(1, (int) floor($damage));
    }

    public function typeMultiplier(PokemonType $attackType, PokemonType ...$defenderTypes): float
    {
        $multiplier = 1.0;

        foreach ($defenderTypes as $defenderType) {
            $multiplier *= $attackType->effectiveness()[$defenderType->value];
        }

        return $multiplier;
    }

    /**
     * @param  array<int, PokemonType>  $attackerTypes
     */
    private function stabMultiplier(PokemonType $moveType, array $attackerTypes): float
    {
        return in_array($moveType, $attackerTypes, true) ? (float) config('game.battle.stab_multiplier') : 1.0;
    }

    private function burnMultiplier(bool $isAttackerBurned): float
    {
        return $isAttackerBurned ? (float) config('game.battle.ailments.burn_damage_done_multiplier') : 1.0;
    }

    private function criticalHitMultiplier(bool $isCriticalHit): float
    {
        return $isCriticalHit ? (float) config('game.battle.critical_hit_multiplier') : 1.0;
    }
}
