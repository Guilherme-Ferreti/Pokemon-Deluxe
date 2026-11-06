<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\Exceptions\IncubatorEggNotReadyToCatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Shared\Enums\IncubationStatus;
use Shared\Models\Incubation;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;
use Shared\ValueObjects\NewPokemon;

final class CatchPokemonAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    private Pokeball $pokeball;

    private Incubation $incubation;

    private Pokemon $pokemon;

    public function handle(User $user, IncubatorIndex $incubatorIndex, Pokeball $pokeball): Pokemon
    {
        $this->user           = $user;
        $this->incubatorIndex = $incubatorIndex;
        $this->pokeball       = $pokeball;

        Cache::withoutOverlapping(
            key: LockKeys::incubatorCatchPokemon($this->user->id, $this->incubatorIndex),
            callback: fn () => $this->catchPokemon()
        );

        return $this->pokemon;
    }

    private function catchPokemon(): void
    {
        $this->ensureIncubatorIsActive();

        $this->loadIncubation();

        $this->ensureEggIsReadyToCatch();

        DB::transaction(function (): void {
            new RemoveInventoryItemAction()->handle($this->user, $this->pokeball);

            $this->pokemon = new CreatePokemonAction()->handle(
                owner: $this->user,
                newPokemon: NewPokemon::fromIncubation($this->incubation),
                pokeball: $this->pokeball
            );

            $this->markEggAsCaught();
        });
    }

    private function ensureIncubatorIsActive(): void
    {
        $availability = new GetIncubatorsAvailabilityAction()->handle($this->user);

        if (value_object_array_contains($availability['active'], $this->incubatorIndex)) {
            return;
        }

        throw new IncubatorNotActiveException($this->incubatorIndex);
    }

    private function loadIncubation(): void
    {
        $this->incubation = $this->user
            ->incubations()
            ->active()
            ->where('incubator_index', $this->incubatorIndex->value)
            ->firstOrFail();
    }

    private function ensureEggIsReadyToCatch(): void
    {
        if ($this->incubation->getStatus() === IncubationStatus::READY_TO_CATCH) {
            return;
        }

        throw new IncubatorEggNotReadyToCatchException($this->incubatorIndex);
    }

    private function markEggAsCaught(): void
    {
        $this->incubation->caught_at = now();
        $this->incubation->save();
    }
}
