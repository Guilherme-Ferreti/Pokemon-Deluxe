<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\Events\PokemonCaught;
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
        $this->loadActiveIncubation();

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

        PokemonCaught::dispatch($this->pokemon->id, $this->user->id);
    }

    private function loadActiveIncubation(): void
    {
        $incubator = new ListIncubatorsAction()->handle($this->user)->get($this->incubatorIndex->value);

        if (! $incubator?->isActive()) {
            throw new IncubatorNotActiveException($this->incubatorIndex);
        }

        $this->incubation = $incubator->incubation;
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
