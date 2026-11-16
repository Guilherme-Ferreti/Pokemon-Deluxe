<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Mechanics\DamageCalculator;
use Closure;

final class CheckTypeImmunity
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $action->typeMultiplier = new DamageCalculator()->typeMultiplier($action->move->type, ...$action->defender()->types);

        if ($action->typeMultiplier !== 0.0) {
            return $next($action);
        }

        $action->turn->log("It doesn't affect {$action->turn->displayName($action->defendingSide(), atStart: false)}...");

        $action->outcome = TurnActionOutcome::NO_EFFECT;

        return $action;
    }
}
