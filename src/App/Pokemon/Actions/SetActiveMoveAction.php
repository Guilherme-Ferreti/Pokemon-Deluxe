<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Exceptions\MoveNotKnownException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Models\Move;
use Shared\Models\Pokemon;

final class SetActiveMoveAction
{
    private Pokemon $pokemon;

    private Move $move;

    private int $slot;

    private ?Move $knownMove = null;

    public function handle(Pokemon $pokemon, Move $move, int $slot): void
    {
        $this->pokemon = $pokemon;
        $this->move    = $move;
        $this->slot    = $slot;

        Cache::withoutOverlapping(
            key: LockKeys::pokemonActiveMoves($pokemon->id),
            callback: fn () => $this->setActiveMove()
        );
    }

    private function setActiveMove(): void
    {
        $this->loadKnownMove();

        $this->ensureSlotExists();
        $this->ensureMoveIsKnown();

        $currentSlot = $this->currentSlotOfMove();

        if ($currentSlot === $this->slot) {
            return;
        }

        $activeMoveIdInSlot = $this->pokemon->knownMoves()->wherePivot('slot', $this->slot)->value('moves.id');

        DB::transaction(function () use ($currentSlot, $activeMoveIdInSlot): void {
            if ($currentSlot !== null) {
                $this->assignSlot($this->move->id, null);
            }

            if ($activeMoveIdInSlot !== null) {
                $this->assignSlot($activeMoveIdInSlot, $currentSlot);
            }

            $this->assignSlot($this->move->id, $this->slot);
        });
    }

    private function loadKnownMove(): void
    {
        $this->knownMove = $this->pokemon->knownMoves()->whereKey($this->move->id)->first();
    }

    private function ensureSlotExists(): void
    {
        $maxActiveMoves = (int) config('game.pokemon.max_active_moves');

        throw_if(
            $this->slot < 1 || $this->slot > $maxActiveMoves,
            InvalidArgumentException::class,
            "The slot must be between 1 and {$maxActiveMoves}, [{$this->slot}] given.",
        );
    }

    private function ensureMoveIsKnown(): void
    {
        throw_unless($this->knownMove instanceof Move, new MoveNotKnownException($this->pokemon, $this->move));
    }

    private function currentSlotOfMove(): ?int
    {
        $slot = $this->knownMove?->getRelationValue('pivot')->slot;

        return $slot === null ? null : (int) $slot;
    }

    private function assignSlot(string $moveId, ?int $slot): void
    {
        $this->pokemon->knownMoves()->updateExistingPivot($moveId, ['slot' => $slot]);
    }
}
