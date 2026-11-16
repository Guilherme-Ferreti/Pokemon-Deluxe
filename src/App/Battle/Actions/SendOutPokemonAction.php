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
use Shared\ValueObjects\Combatant;

final class SendOutPokemonAction
{
    private Battle $battle;

    private int $combatantIndex;

    public function handle(Battle $battle, int $combatantIndex): Battle
    {
        $this->battle         = $battle;
        $this->combatantIndex = $combatantIndex;

        return Cache::withoutOverlapping(
            key: LockKeys::battle($battle->id),
            callback: fn () => $this->sendOut(),
        );
    }

    private function sendOut(): Battle
    {
        $this->battle->refresh();

        $this->ensureBattleIsAwaitingSendOut();
        $this->ensureCombatantIsInTeam();
        $this->ensureCombatantHasNotFainted();

        $playerSide = $this->battle->state->player_side->withActiveIndex($this->combatantIndex);
        $playerSide = $playerSide->withActiveCombatant($playerSide->activeCombatant()->withEnteredField());

        $this->battle->update([
            'state' => $this->battle->state
                ->withPlayerSide($playerSide)
                ->withAwaitingSendOut(false)
                ->withLogLines(["Go! {$this->chosenDisplayName()}!"]),
        ]);

        return $this->battle;
    }

    private function ensureBattleIsAwaitingSendOut(): void
    {
        throw_unless(
            $this->battle->status === BattleStatus::ONGOING && $this->battle->state->is_awaiting_send_out,
            BattleNotAwaitingSendOutException::class,
        );
    }

    private function ensureCombatantIsInTeam(): void
    {
        $combatants = $this->battle->state->player_side->combatants;

        throw_unless(
            isset($combatants[$this->combatantIndex]),
            InvalidArgumentException::class,
            sprintf('The combatant index must be between 0 and %d, [%d] given.', count($combatants) - 1, $this->combatantIndex),
        );
    }

    private function ensureCombatantHasNotFainted(): void
    {
        throw_if(
            $this->chosenCombatant()->isFainted(),
            fn () => new PokemonHasFaintedException($this->chosenDisplayName()),
        );
    }

    private function chosenCombatant(): Combatant
    {
        return $this->battle->state->player_side->combatants[$this->combatantIndex];
    }

    private function chosenDisplayName(): string
    {
        return Species::query()
            ->findOrFail($this->chosenCombatant()->species_id)
            ->getDisplayName($this->chosenCombatant()->is_shiny);
    }
}
