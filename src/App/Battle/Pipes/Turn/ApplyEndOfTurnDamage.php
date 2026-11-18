<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Mechanics\StatusEffectCalculator;
use Closure;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;

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

            $hpLost = $turn->damageActiveCombatant($side, $damage);

            $cause = $combatant->ailment === Ailment::BURN ? 'its burn' : 'poison';

            $turn->log(BattleLogKind::DETAIL, "{$turn->displayName($side, atStart: true)} is hurt by {$cause}!");
            $turn->logDamage($hpLost);

            $turn->logFaintIfFainted($side);
        }

        return $next($turn);
    }
}
