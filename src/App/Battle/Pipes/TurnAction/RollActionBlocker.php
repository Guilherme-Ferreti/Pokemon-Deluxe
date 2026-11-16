<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Mechanics\StatusEffectRoller;
use Closure;

final class RollActionBlocker
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $action->blocker = new StatusEffectRoller()->rollActionBlocker($action->attacker());

        return $next($action);
    }
}
