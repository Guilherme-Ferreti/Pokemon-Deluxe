<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\TurnActionOutcome;
use Closure;

final class SkipFlinchedAttacker
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        if ($action->turn->actionOf($action->defendingSide())?->outcome !== TurnActionOutcome::CAUSED_FLINCH) {
            return $next($action);
        }

        $action->turn->log("{$action->turn->displayName($action->side, atStart: true)} flinched and couldn't move!");

        $action->outcome = TurnActionOutcome::BLOCKED;

        return $action;
    }
}
