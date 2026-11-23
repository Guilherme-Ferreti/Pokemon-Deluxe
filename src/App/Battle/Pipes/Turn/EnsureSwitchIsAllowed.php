<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Exceptions\InvalidSwitchException;
use Closure;
use LogicException;
use Shared\ValueObjects\Combatant;

final class EnsureSwitchIsAllowed
{
    private TurnData $turn;

    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $this->turn = $turn;

        $this->ensureCombatantIsInTeam();
        $this->ensureCombatantIsNotInBattle();
        $this->ensureCombatantHasNotFainted();

        return $next($turn);
    }

    private function ensureCombatantIsInTeam(): void
    {
        throw_unless(
            isset($this->turn->state->playerSide->combatants[$this->turn->chosenSwitchIndex()]),
            fn () => InvalidSwitchException::notInTeam(),
        );
    }

    private function ensureCombatantIsNotInBattle(): void
    {
        throw_if(
            $this->turn->chosenSwitchIndex() === $this->turn->state->playerSide->activeIndex,
            fn () => InvalidSwitchException::alreadyInBattle($this->chosenDisplayName()),
        );
    }

    private function ensureCombatantHasNotFainted(): void
    {
        throw_if(
            $this->chosenCombatant()->isFainted(),
            fn () => InvalidSwitchException::fainted($this->chosenDisplayName()),
        );
    }

    private function chosenCombatant(): Combatant
    {
        return $this->turn->state->playerSide->combatants[$this->turn->chosenSwitchIndex()]
            ?? throw new LogicException('The combatant must be checked first.');
    }

    private function chosenDisplayName(): string
    {
        return $this->turn->species
            ->findOrFail($this->chosenCombatant()->speciesId)
            ->getDisplayName($this->chosenCombatant()->isShiny);
    }
}
