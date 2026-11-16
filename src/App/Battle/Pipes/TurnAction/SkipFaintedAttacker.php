<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\TurnActionOutcome;
use Closure;

final class SkipFaintedAttacker
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        if (! $action->attacker()->isFainted()) {
            return $next($action);
        }

        $action->outcome = TurnActionOutcome::SKIPPED;

        return $action;
    }
}
