<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Data\TurnData;
use App\Battle\Pipes\Turn\ApplyEndOfTurnDamage;
use App\Battle\Pipes\Turn\ChooseOpponentMove;
use App\Battle\Pipes\Turn\CreditOpponentKnockout;
use App\Battle\Pipes\Turn\EnsureBattleIsReadyForTurn;
use App\Battle\Pipes\Turn\EnsureSwitchIsAllowed;
use App\Battle\Pipes\Turn\LoadMovesAndSpecies;
use App\Battle\Pipes\Turn\SaveTurn;
use App\Battle\Pipes\Turn\SwitchPlayerPokemon;
use App\Battle\Pipes\Turn\TakeActions;
use App\Battle\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Pipeline;
use Shared\Models\Battle;

final class SwitchPokemonAction
{
    private Battle $battle;

    private int $combatantIndex;

    public function handle(Battle $battle, int $combatantIndex): Battle
    {
        $this->battle         = $battle;
        $this->combatantIndex = $combatantIndex;

        return Cache::withoutOverlapping(
            key: LockKeys::battle($battle->id),
            callback: fn () => $this->switch(),
        );
    }

    private function switch(): Battle
    {
        $this->battle->refresh();

        $turn = new TurnData($this->battle, switchIndex: $this->combatantIndex);

        Pipeline::send($turn)
            ->through([
                EnsureBattleIsReadyForTurn::class,
                LoadMovesAndSpecies::class,
                EnsureSwitchIsAllowed::class,
                SwitchPlayerPokemon::class,
                ChooseOpponentMove::class,
                TakeActions::class,
                ApplyEndOfTurnDamage::class,
                CreditOpponentKnockout::class,
                SaveTurn::class,
            ])
            ->thenReturn();

        return $turn->battle;
    }
}
