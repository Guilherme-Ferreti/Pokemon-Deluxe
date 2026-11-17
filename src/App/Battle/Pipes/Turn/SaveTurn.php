<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Actions\EndBattleAction;
use App\Battle\Data\TurnData;
use Closure;
use Illuminate\Support\Facades\DB;
use Shared\Enums\BattleStatus;

final class SaveTurn
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $endStatus = match (true) {
            ! $turn->state->opponent_side->hasCombatantsLeft() => BattleStatus::WON,
            ! $turn->state->player_side->hasCombatantsLeft()   => BattleStatus::LOST,
            default                                            => null,
        };

        $state = $turn->state->withNextTurn($turn->logEntries);

        if ($endStatus === null && $state->player_side->activeCombatant()->isFainted()) {
            $state = $state->withAwaitingSendOut(true);
        }

        DB::transaction(function () use ($turn, $state, $endStatus): void {
            $turn->battle->update(['state' => $state]);

            if ($endStatus instanceof BattleStatus) {
                new EndBattleAction()->handle($turn->battle, $endStatus);
            }
        });

        return $next($turn);
    }
}
