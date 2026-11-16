<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Data\TurnData;
use App\Battle\Pipes\Turn\ApplyEndOfTurnDamage;
use App\Battle\Pipes\Turn\ChooseOpponentMove;
use App\Battle\Pipes\Turn\EnsureBattleIsReadyForTurn;
use App\Battle\Pipes\Turn\EnsurePlayerMoveIsUsable;
use App\Battle\Pipes\Turn\LoadMovesAndSpecies;
use App\Battle\Pipes\Turn\RollTurnOrder;
use App\Battle\Pipes\Turn\SaveTurn;
use App\Battle\Pipes\Turn\TakeActions;
use App\Battle\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Pipeline;
use Shared\Models\Battle;

final class TakeTurnAction
{
    private Battle $battle;

    private int $moveSlot;

    public function handle(Battle $battle, int $moveSlot): Battle
    {
        $this->battle   = $battle;
        $this->moveSlot = $moveSlot;

        return Cache::withoutOverlapping(
            key: LockKeys::battle($battle->id),
            callback: fn () => $this->takeTurn(),
        );
    }

    private function takeTurn(): Battle
    {
        $this->battle->refresh();

        $turn = new TurnData($this->battle, $this->moveSlot);

        Pipeline::send($turn)
            ->through([
                EnsureBattleIsReadyForTurn::class,
                LoadMovesAndSpecies::class,
                EnsurePlayerMoveIsUsable::class,
                ChooseOpponentMove::class,
                RollTurnOrder::class,
                TakeActions::class,
                ApplyEndOfTurnDamage::class,
                SaveTurn::class,
            ])
            ->thenReturn();

        return $turn->battle;
    }
}
