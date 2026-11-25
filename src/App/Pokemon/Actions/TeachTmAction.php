<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Pokemon\Exceptions\MoveAlreadyKnownException;
use App\Pokemon\Exceptions\TmRequirementNotMetException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Shared\Models\Move;
use Shared\Models\Pokemon;

final class TeachTmAction
{
    private Pokemon $pokemon;

    private Move $move;

    public function handle(Pokemon $pokemon, Move $move): void
    {
        $this->pokemon = $pokemon;
        $this->move    = $move;

        Cache::withoutOverlapping(
            key: LockKeys::pokemonActiveMoves($pokemon->id),
            callback: fn () => $this->teachTm(),
        );
    }

    private function teachTm(): void
    {
        $this->pokemon->refresh();

        $this->ensureMoveIsNotKnown();
        $this->ensureTmRequirementIsMet();

        DB::transaction(function (): void {
            new RemoveInventoryItemAction()->handle($this->pokemon->user, $this->move);

            $this->pokemon->knownMoves()->attach($this->move->id, ['slot' => $this->firstEmptySlot()]);
        });
    }

    private function ensureMoveIsNotKnown(): void
    {
        throw_if(
            $this->pokemon->knownMoves()->whereKey($this->move->id)->exists(),
            fn () => new MoveAlreadyKnownException($this->pokemon, $this->move),
        );
    }

    private function ensureTmRequirementIsMet(): void
    {
        throw_if(
            $this->needsMaxLevel() && $this->pokemon->level < $this->maxLevel(),
            fn () => new TmRequirementNotMetException($this->move, $this->maxLevel()),
        );
    }

    private function needsMaxLevel(): bool
    {
        return $this->move->is_z_move || $this->move->is_dynamax_move;
    }

    private function maxLevel(): int
    {
        return (int) config('game.pokemon.max_level');
    }

    private function firstEmptySlot(): ?int
    {
        $takenSlots = $this->pokemon->knownMoves()->wherePivotNotNull('slot')->pluck('pokemon_move.slot')->map(fn (mixed $slot) => (int) $slot)->all();

        return collect(range(1, (int) config('game.pokemon.max_active_moves')))
            ->first(fn (int $slot) => ! in_array($slot, $takenSlots, true));
    }
}
