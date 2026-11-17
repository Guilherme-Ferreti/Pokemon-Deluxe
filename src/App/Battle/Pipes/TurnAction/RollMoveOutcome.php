<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Mechanics\MoveOutcomeRoller;
use Closure;
use Shared\Enums\BattleLogKind;

final class RollMoveOutcome
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $action->moveOutcome = new MoveOutcomeRoller()->roll($action->move);

        if ($action->moveOutcome->hasHit) {
            return $next($action);
        }

        $action->turn->log(BattleLogKind::EVENT, "{$action->turn->displayName($action->side, atStart: true)}'s attack missed!");

        $action->outcome = TurnActionOutcome::MISSED;

        return $action;
    }
}
