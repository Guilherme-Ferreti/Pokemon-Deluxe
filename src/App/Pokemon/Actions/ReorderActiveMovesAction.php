<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Models\Pokemon;

final class ReorderActiveMovesAction
{
    private Pokemon $pokemon;

    /**
     * @var list<string>
     */
    private array $moveIds;

    /**
     * @var list<string>
     */
    private array $activeMoveIds = [];

    /**
     * @param  list<string>  $moveIds
     */
    public function handle(Pokemon $pokemon, array $moveIds): void
    {
        $this->pokemon = $pokemon;
        $this->moveIds = $moveIds;

        Cache::withoutOverlapping(
            key: LockKeys::pokemonActiveMoves($pokemon->id),
            callback: fn () => $this->reorderActiveMoves(),
        );
    }

    private function reorderActiveMoves(): void
    {
        $this->loadActiveMoveIds();

        $this->ensureMoveIdsAreTheActiveMoves();

        DB::transaction(function (): void {
            foreach ($this->activeMoveIds as $moveId) {
                $this->pokemon->knownMoves()->updateExistingPivot($moveId, ['slot' => null]);
            }

            foreach ($this->moveIds as $index => $moveId) {
                $this->pokemon->knownMoves()->updateExistingPivot($moveId, ['slot' => $index + 1]);
            }
        });
    }

    private function loadActiveMoveIds(): void
    {
        $this->activeMoveIds = array_values(array_map(
            strval(...),
            $this->pokemon->knownMoves()->wherePivotNotNull('slot')->pluck('moves.id')->all(),
        ));
    }

    private function ensureMoveIdsAreTheActiveMoves(): void
    {
        $sortedMoveIds       = $this->moveIds;
        $sortedActiveMoveIds = $this->activeMoveIds;

        sort($sortedMoveIds);
        sort($sortedActiveMoveIds);

        throw_if(
            $sortedMoveIds !== $sortedActiveMoveIds,
            InvalidArgumentException::class,
            'The move ids must be exactly the active moves of the Pokémon.',
        );
    }
}
