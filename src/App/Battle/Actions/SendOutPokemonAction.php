<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Exceptions\BattleNotAwaitingSendOutException;
use App\Battle\Exceptions\PokemonHasFaintedException;
use App\Battle\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Shared\Enums\BattleStatus;
use Shared\Models\Battle;
use Shared\Models\Species;

final class SendOutPokemonAction
{
    private Battle $battle;

    public function handle(Battle $battle, int $combatantIndex): Battle
    {
        $this->battle = $battle;

        return Cache::withoutOverlapping(
            key: LockKeys::battle($battle->id),
            callback: fn () => $this->sendOut($combatantIndex),
        );
    }

    private function sendOut(int $combatantIndex): Battle
    {
        $this->battle->refresh();

        $state = $this->battle->state;

        throw_unless(
            $this->battle->status === BattleStatus::ONGOING && $state->is_awaiting_send_out,
            BattleNotAwaitingSendOutException::class,
        );

        $combatants = $state->player_side->combatants;

        throw_unless(
            isset($combatants[$combatantIndex]),
            InvalidArgumentException::class,
            sprintf('The combatant index must be between 0 and %d, [%d] given.', count($combatants) - 1, $combatantIndex),
        );

        $species     = Species::query()->findOrFail($combatants[$combatantIndex]->species_id);
        $displayName = $species->getDisplayName($combatants[$combatantIndex]->is_shiny);

        throw_if($combatants[$combatantIndex]->isFainted(), new PokemonHasFaintedException($displayName));

        $playerSide = $state->player_side->withActiveIndex($combatantIndex);
        $playerSide = $playerSide->withActiveCombatant($playerSide->activeCombatant()->withEnteredField());

        $this->battle->update([
            'state' => $state
                ->withPlayerSide($playerSide)
                ->withAwaitingSendOut(false)
                ->withLogLines(["Go! {$displayName}!"]),
        ]);

        return $this->battle;
    }
}
