<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Mechanics\MoveUsabilityChecker;
use Closure;

final class AnnounceMove
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $move = $action->move;

        $action->turn->log("{$action->turn->displayName($action->side, atStart: true)} used {$move->name}!");

        if (new MoveUsabilityChecker()->isOncePerBattle($move)) {
            $action->turn->replaceActiveCombatant($action->side, $action->attacker()->withUsedOncePerBattleMove($move->id));
        }

        return $next($action);
    }
}
