<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Enums\Side;
use App\Battle\Mechanics\TurnOrderRoller;
use Closure;

final class RollTurnOrder
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $isPlayerFirst = new TurnOrderRoller()->rollIsPlayerFirst(
            $turn->state->player_side->activeCombatant()->level,
            $turn->state->opponent_side->activeCombatant()->level,
        );

        $turn->turnOrder = $isPlayerFirst ? [Side::PLAYER, Side::OPPONENT] : [Side::OPPONENT, Side::PLAYER];

        return $next($turn);
    }
}
