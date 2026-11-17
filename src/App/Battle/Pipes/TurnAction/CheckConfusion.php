<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\ActionBlocker;
use Closure;
use Shared\Enums\BattleLogKind;

final class CheckConfusion
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $attacker = $action->attacker();

        $action->isConfusionChecked = $attacker->confusion_turns_left > 0
            && ! in_array($action->blocker, [ActionBlocker::FROZEN, ActionBlocker::ASLEEP], true);

        if ($action->isConfusionChecked) {
            $action->turn->log(BattleLogKind::EVENT, "{$action->turn->displayName($action->side, atStart: true)} is confused!");

            $action->turn->replaceActiveCombatant($action->side, $attacker->withConfusionTurnsLeft($attacker->confusion_turns_left - 1));
        }

        return $next($action);
    }
}
