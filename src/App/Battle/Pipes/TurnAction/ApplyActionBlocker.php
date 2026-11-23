<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\ActionBlocker;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Mechanics\StatusEffectCalculator;
use App\Pokemon\Mechanics\StatsCalculator;
use Closure;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;

final class ApplyActionBlocker
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        if (! $action->blocker instanceof ActionBlocker) {
            return $next($action);
        }

        $attacker = $action->attacker();
        $name     = $action->turn->displayName($action->side, atStart: true);

        $action->turn->log(BattleLogKind::EVENT, match ($action->blocker) {
            ActionBlocker::FROZEN                   => "{$name} is frozen solid!",
            ActionBlocker::ASLEEP                   => "{$name} is fast asleep.",
            ActionBlocker::HURT_ITSELF_IN_CONFUSION => 'It hurt itself in its confusion!',
            ActionBlocker::PARALYZED                => "{$name} is paralyzed! It can't move!",
        });

        if ($action->blocker === ActionBlocker::ASLEEP) {
            $action->turn->replaceActiveCombatant($action->side, $attacker->withAilment(Ailment::SLEEP, $attacker->ailmentTurnsLeft - 1));
        }

        if ($action->blocker === ActionBlocker::HURT_ITSELF_IN_CONFUSION) {
            $damage = new StatusEffectCalculator()->confusionSelfHitDamage(
                new StatsCalculator()->calculate($attacker->level, $attacker->boost, $attacker->isShiny),
            );

            $action->turn->logDamage($action->turn->damageActiveCombatant($action->side, $damage));

            $action->turn->logFaintIfFainted($action->side);
        }

        $action->outcome = TurnActionOutcome::BLOCKED;

        return $action;
    }
}
