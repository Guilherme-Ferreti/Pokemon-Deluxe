<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Enums\Side;
use Closure;

final class CreditOpponentKnockout
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $opponent = $turn->activeCombatant(Side::OPPONENT);

        if ($opponent->isFainted() && $opponent->knocked_out_by === null) {
            $turn->replaceActiveCombatant(Side::OPPONENT, $opponent->withKnockedOutBy($turn->state->player_side->active_index));
        }

        return $next($turn);
    }
}
