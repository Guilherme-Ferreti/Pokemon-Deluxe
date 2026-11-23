<?php

declare(strict_types=1);

namespace App\Battle\Pipes\Turn;

use App\Battle\Data\TurnData;
use App\Battle\Exceptions\DynamaxMoveNotAllowedException;
use App\Battle\Exceptions\OncePerBattleMoveAlreadyUsedException;
use App\Battle\Mechanics\MoveUsabilityChecker;
use Closure;
use InvalidArgumentException;
use Shared\ValueObjects\Combatant;

final class EnsurePlayerMoveIsUsable
{
    private TurnData $turn;

    /**
     * @param  Closure(TurnData): TurnData  $next
     */
    public function handle(TurnData $turn, Closure $next): TurnData
    {
        $this->turn = $turn;

        $this->ensureMoveSlotExists();
        $this->ensureOncePerBattleMoveIsNotAlreadyUsed();
        $this->ensureDynamaxMoveIsAllowed();

        return $next($turn);
    }

    private function ensureMoveSlotExists(): void
    {
        $moveIds = $this->playerCombatant()->moveIds;

        throw_unless(
            isset($moveIds[$this->turn->moveSlot - 1]),
            InvalidArgumentException::class,
            sprintf('The move slot must be between 1 and %d, [%d] given.', count($moveIds), $this->turn->moveSlot),
        );
    }

    private function ensureOncePerBattleMoveIsNotAlreadyUsed(): void
    {
        throw_if(
            new MoveUsabilityChecker()->isOncePerBattleMoveAlreadyUsed($this->turn->playerMove(), $this->playerCombatant()),
            fn () => new OncePerBattleMoveAlreadyUsedException($this->turn->playerMove()),
        );
    }

    private function ensureDynamaxMoveIsAllowed(): void
    {
        $species = $this->turn->species->findOrFail($this->playerCombatant()->speciesId);

        throw_unless(
            new MoveUsabilityChecker()->isDynamaxMoveAllowed($this->turn->playerMove(), $species),
            fn () => new DynamaxMoveNotAllowedException($this->turn->playerMove()),
        );
    }

    private function playerCombatant(): Combatant
    {
        return $this->turn->state->playerSide->activeCombatant();
    }
}
