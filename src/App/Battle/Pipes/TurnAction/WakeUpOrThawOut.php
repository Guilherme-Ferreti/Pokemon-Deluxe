<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\ActionBlocker;
use Closure;
use Shared\Enums\Ailment;

final class WakeUpOrThawOut
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $attacker = $action->attacker();

        $logLine = match (true) {
            $attacker->ailment === Ailment::FREEZE && $action->blocker !== ActionBlocker::FROZEN => 'thawed out!',
            $attacker->ailment === Ailment::SLEEP && $action->blocker !== ActionBlocker::ASLEEP  => 'woke up!',
            default                                                                              => null,
        };

        if ($logLine !== null) {
            $action->turn->replaceActiveCombatant($action->side, $attacker->withAilment(null, 0));

            $action->turn->log("{$action->turn->displayName($action->side, atStart: true)} {$logLine}");
        }

        return $next($action);
    }
}
