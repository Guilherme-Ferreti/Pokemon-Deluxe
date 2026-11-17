<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Mechanics\MoveUsabilityChecker;
use Closure;
use Shared\Enums\BattleLogKind;

final class AnnounceMove
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $move = $action->move;

        $action->turn->log(BattleLogKind::MOVE, "{$action->turn->displayName($action->side, atStart: true)} used {$move->name}!", $move->name);

        if (new MoveUsabilityChecker()->isOncePerBattle($move)) {
            $action->turn->replaceActiveCombatant($action->side, $action->attacker()->withUsedOncePerBattleMove($move->id));
        }

        return $next($action);
    }
}
