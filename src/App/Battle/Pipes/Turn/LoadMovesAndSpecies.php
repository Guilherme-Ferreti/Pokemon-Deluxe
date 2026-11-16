<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use Closure;
use Shared\Models\Move;
use Shared\Models\Species;

final class LoadMovesAndSpecies
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $playerCombatant   = $turn->state->player_side->activeCombatant();
        $opponentCombatant = $turn->state->opponent_side->activeCombatant();

        $turn->moves = Move::query()
            ->whereKey([...$playerCombatant->move_ids, ...$opponentCombatant->move_ids])
            ->get()
            ->keyBy('id');

        $turn->species = Species::query()
            ->whereKey([$playerCombatant->species_id, $opponentCombatant->species_id])
            ->get(['id', 'name', 'is_gmax'])
            ->keyBy('id');

        return $next($turn);
    }
}
