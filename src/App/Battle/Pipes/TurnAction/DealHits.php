<?php

declare(strict_types=1);

namespace App\Battle\Pipes\TurnAction;

use App\Battle\Data\TurnActionData;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Mechanics\DamageCalculator;
use App\Battle\ValueObjects\MoveHit;
use App\Pokemon\Mechanics\StatsCalculator;
use Closure;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;

final class DealHits
{
    /**
     * @param  Closure(TurnActionData): TurnActionData  $next
     */
    public function handle(TurnActionData $action, Closure $next): TurnActionData
    {
        if ($action->move->power === null) {
            return $next($action);
        }

        $landedHits = 0;
        $hpLost     = 0;

        foreach ($action->rolledMoveOutcome()->hits as $hit) {
            if ($action->defender()->isFainted()) {
                break;
            }

            $hpLost += $this->dealHit($action, $hit);

            $landedHits++;
        }

        $this->logEffectiveness($action);

        if ($action->rolledMoveOutcome()->hitCount() > 1) {
            $action->turn->log(BattleLogKind::DETAIL, $landedHits === 1 ? 'It hit once!' : "It hit {$landedHits} times!");
        }

        $action->turn->logDamage($hpLost);

        if (! $action->turn->logFaintIfFainted($action->defendingSide())) {
            return $next($action);
        }

        $action->outcome = TurnActionOutcome::KNOCKED_OUT;

        return $action;
    }

    private function dealHit(TurnActionData $action, MoveHit $hit): int
    {
        $attacker = $action->attacker();
        $defender = $action->defender();

        $damage = new DamageCalculator()->calculate(
            attackerStats: new StatsCalculator()->calculate($attacker->level, $attacker->boost, $attacker->is_shiny),
            attackerTypes: $attacker->types,
            defenderStats: new StatsCalculator()->calculate($defender->level, $defender->boost, $defender->is_shiny),
            defenderTypes: $defender->types,
            move: $action->move,
            isCriticalHit: $hit->isCriticalHit,
            randomFactor: $hit->randomFactor,
            isAttackerBurned: $attacker->ailment === Ailment::BURN,
        );

        if ($hit->isCriticalHit) {
            $action->turn->log(BattleLogKind::DETAIL, 'A critical hit!');
        }

        return $action->turn->damageActiveCombatant($action->defendingSide(), $damage);
    }

    private function logEffectiveness(TurnActionData $action): void
    {
        if ($action->typeMultiplier > 1.0) {
            $action->turn->log(BattleLogKind::DETAIL, "It's super effective!");
        } elseif ($action->typeMultiplier < 1.0) {
            $action->turn->log(BattleLogKind::DETAIL, "It's not very effective...");
        }
    }
}
