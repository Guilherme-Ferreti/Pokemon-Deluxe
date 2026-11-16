<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Mechanics\StatusEffectCalculator;
use Closure;
use Shared\Enums\Ailment;

final class ApplyEndOfTurnDamage
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        foreach ($turn->turnOrder as $side) {
            if (! $turn->state->player_side->hasCombatantsLeft() || ! $turn->state->opponent_side->hasCombatantsLeft()) {
                break;
            }

            $combatant = $turn->activeCombatant($side);
            $damage    = new StatusEffectCalculator()->endOfTurnDamage($combatant);

            if ($damage === 0) {
                continue;
            }

            $turn->damageActiveCombatant($side, $damage);

            $cause = $combatant->ailment === Ailment::BURN ? 'its burn' : 'poison';

            $turn->log("{$turn->displayName($side, atStart: true)} is hurt by {$cause}!");

            $turn->logFaintIfFainted($side);
        }

        return $next($turn);
    }
}
