<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Enums\Side;
use Closure;

final class SwitchPlayerPokemon
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $turn->log("Come back, {$turn->displayName(Side::PLAYER, atStart: false)}!");

        $turn->replaceActiveCombatant(Side::PLAYER, $turn->activeCombatant(Side::PLAYER)->withConfusionTurnsLeft(0));
        $turn->switchPlayerActiveIndex($turn->chosenSwitchIndex());
        $turn->replaceActiveCombatant(Side::PLAYER, $turn->activeCombatant(Side::PLAYER)->withEnteredField());

        $turn->log("Go! {$turn->displayName(Side::PLAYER, atStart: false)}!");

        $turn->turnOrder = [Side::PLAYER, Side::OPPONENT];

        return $next($turn);
    }
}
