<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnActionData;
use App\Battle\Data\TurnData;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\Pipes\TurnAction\AnnounceMove;
use App\Battle\Pipes\TurnAction\ApplyActionBlocker;
use App\Battle\Pipes\TurnAction\CheckConfusion;
use App\Battle\Pipes\TurnAction\CheckTypeImmunity;
use App\Battle\Pipes\TurnAction\DealHits;
use App\Battle\Pipes\TurnAction\InflictAilment;
use App\Battle\Pipes\TurnAction\RollActionBlocker;
use App\Battle\Pipes\TurnAction\RollMoveOutcome;
use App\Battle\Pipes\TurnAction\SkipFaintedAttacker;
use App\Battle\Pipes\TurnAction\SkipFlinchedAttacker;
use App\Battle\Pipes\TurnAction\WakeUpOrThawOut;
use Closure;
use Illuminate\Support\Facades\Pipeline;

final class TakeActions
{
    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        foreach ($turn->turnOrder as $side) {
            if (! $turn->isUsingMove($side)) {
                continue;
            }

            $action = $turn->startActionFor($side);

            Pipeline::send($action)
                ->through([
                    SkipFaintedAttacker::class,
                    SkipFlinchedAttacker::class,
                    RollActionBlocker::class,
                    WakeUpOrThawOut::class,
                    CheckConfusion::class,
                    ApplyActionBlocker::class,
                    AnnounceMove::class,
                    RollMoveOutcome::class,
                    CheckTypeImmunity::class,
                    DealHits::class,
                    InflictAilment::class,
                ])
                ->then(fn (TurnActionData $action) => $this->finishMove($action));

            $this->logSnapOutOfConfusion($action);
        }

        return $next($turn);
    }

    private function finishMove(TurnActionData $action): TurnActionData
    {
        $action->outcome = $action->rolledMoveOutcome()->causesFlinch ? TurnActionOutcome::CAUSED_FLINCH : TurnActionOutcome::USED;

        return $action;
    }

    private function logSnapOutOfConfusion(TurnActionData $action): void
    {
        $attacker = $action->attacker();

        if ($action->isConfusionChecked && $attacker->confusion_turns_left === 0 && ! $attacker->isFainted()) {
            $action->turn->log("{$action->turn->displayName($action->side, atStart: true)} snapped out of its confusion!");
        }
    }
}
