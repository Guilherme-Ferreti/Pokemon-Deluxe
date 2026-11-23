<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Mechanics\MoveUsabilityChecker;
use App\Battle\Mechanics\OpponentMoveRoller;
use Closure;
use LogicException;

final class ChooseOpponentMove
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $combatant = $turn->state->opponentSide->activeCombatant();
        $species   = $turn->species->findOrFail($combatant->speciesId);

        $usableMoveIds = array_filter(
            $combatant->moveIds,
            fn (string $moveId) => new MoveUsabilityChecker()->isUsable($turn->moves->findOrFail($moveId), $combatant, $species),
        ) |> array_values(...);

        if ($usableMoveIds === []) {
            throw new LogicException('The opponent has no move it can use.');
        }

        $turn->opponentMove = $turn->moves->findOrFail(new OpponentMoveRoller()->rollMoveId($usableMoveIds));

        return $next($turn);
    }
}
