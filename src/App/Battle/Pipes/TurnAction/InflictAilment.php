<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Mechanics\AilmentInflictionChecker;
use App\Battle\Mechanics\StatusEffectRoller;
use Closure;
use LogicException;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;

final class InflictAilment
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        $ailment = $action->rolledMoveOutcome()->inflictedAilment;

        if (! $ailment instanceof Ailment) {
            return $next($action);
        }

        if (new AilmentInflictionChecker()->canInflict($ailment, $action->defender())) {
            $this->inflict($action, $ailment);

            return $next($action);
        }

        if ($action->move->power !== null) {
            return $next($action);
        }

        $action->turn->log(BattleLogKind::DETAIL, 'But it failed!');

        $action->outcome = TurnActionOutcome::NO_EFFECT;

        return $action;
    }

    private function inflict(TurnActionData $action, Ailment $ailment): void
    {
        $defender  = $action->defender();
        $turnsLeft = new StatusEffectRoller()->durationFor($ailment);

        $action->turn->replaceActiveCombatant($action->defendingSide(), $ailment === Ailment::CONFUSION
            ? $defender->withConfusionTurnsLeft($turnsLeft)
            : $defender->withAilment($ailment, $turnsLeft));

        $logLine = match ($ailment) {
            Ailment::BURN      => 'was burned!',
            Ailment::POISON    => 'was poisoned!',
            Ailment::PARALYSIS => 'is paralyzed! It may be unable to move!',
            Ailment::SLEEP     => 'fell asleep!',
            Ailment::FREEZE    => 'was frozen solid!',
            Ailment::CONFUSION => 'became confused!',
            Ailment::UNKNOWN   => throw new LogicException('The unknown ailment is never inflicted.'),
        };

        $action->turn->log(BattleLogKind::EVENT, "{$action->turn->displayName($action->defendingSide(), atStart: true)} {$logLine}");
    }
}
