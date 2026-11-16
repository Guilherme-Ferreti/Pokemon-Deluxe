<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\Events\PokemonCaught;
use App\Pokemon\Exceptions\IncubatorEggNotReadyToCatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use App\Pokemon\Support\Incubator;
use App\Pokemon\Support\LockKeys;
use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use LogicException;
use Shared\Enums\IncubationStatus;
use Shared\Models\Incubation;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class CatchPokemonAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    private Pokeball $pokeball;

    private ?Incubator $incubator = null;

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
        $this->loadIncubator();

        $this->ensureIncubatorIsActive();
        $this->ensureEggIsReadyToCatch();

        DB::transaction(function (): void {
            new RemoveInventoryItemAction()->handle($this->user, $this->pokeball);

            $this->pokemon = new CreatePokemonAction()->handle(
                owner: $this->user,
                newPokemon: NewPokemon::fromIncubation($this->activeIncubation()),
                pokeball: $this->pokeball
            );

            $this->markEggAsCaught();
        });

        PokemonCaught::dispatch($this->pokemon->id, $this->user->id);
    }

    private function loadIncubator(): void
    {
        $this->incubator = new ListIncubatorsAction()->handle($this->user)->get($this->incubatorIndex->value);
    }

    private function ensureIncubatorIsActive(): void
    {
        throw_unless($this->incubator?->isActive(), new IncubatorNotActiveException($this->incubatorIndex));
    }

    private function ensureEggIsReadyToCatch(): void
    {
        throw_unless(
            $this->activeIncubation()->getStatus() === IncubationStatus::READY_TO_CATCH,
            new IncubatorEggNotReadyToCatchException($this->incubatorIndex),
        );
    }

    private function activeIncubation(): Incubation
    {
        return $this->incubator->incubation ?? throw new LogicException('The incubator must be checked as active first.');
    }

    private function markEggAsCaught(): void
    {
        $incubation = $this->activeIncubation();

        $incubation->caught_at = now();
        $incubation->save();
    }
}
