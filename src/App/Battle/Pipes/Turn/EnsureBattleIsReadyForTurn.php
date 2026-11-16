<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use Closure;
use Shared\Enums\BattleStatus;

final class EnsureBattleIsReadyForTurn
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $isReady = $turn->battle->status === BattleStatus::ONGOING && ! $turn->state->is_awaiting_send_out;

        throw_unless($isReady, BattleNotReadyForTurnException::class);

        return $next($turn);
    }
}
