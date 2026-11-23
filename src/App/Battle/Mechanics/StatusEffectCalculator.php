<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Enums\Ailment;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\PokemonStats;

final class StatusEffectCalculator
{
    public function endOfTurnDamage(Combatant $combatant): int
    {
        if ($combatant->isFainted()) {
            return 0;
        }

        $maxHpPercentage = match ($combatant->ailment) {
            Ailment::BURN   => (float) config('game.battle.ailments.burn_damage_percentage'),
            Ailment::POISON => (float) config('game.battle.ailments.poison_damage_percentage'),
            default         => 0.0,
        };

        if ($maxHpPercentage === 0.0) {
            return 0;
        }

        return max(1, (int) floor($combatant->maxHp * $maxHpPercentage / 100));
    }

    public function confusionSelfHitDamage(PokemonStats $stats): int
    {
        $damage = (int) config('game.battle.ailments.confusion_self_hit_power') * $stats->attack * $stats->defense;

        return max(1, (int) floor($damage));
    }
}
