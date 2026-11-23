<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use Closure;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\Combatant;

final class LoadMovesAndSpecies
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $playerCombatant   = $turn->state->playerSide->activeCombatant();
        $opponentCombatant = $turn->state->opponentSide->activeCombatant();

        $turn->moves = Move::query()
            ->whereKey([...$playerCombatant->moveIds, ...$opponentCombatant->moveIds])
            ->get()
            ->keyBy('id');

        $turn->species = Species::query()
            ->whereKey([
                ...array_map(fn (Combatant $combatant) => $combatant->speciesId, $turn->state->playerSide->combatants),
                $opponentCombatant->speciesId,
            ])
            ->get(['id', 'name', 'is_gmax'])
            ->keyBy('id');

        return $next($turn);
    }
}
