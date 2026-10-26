<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Exceptions\IncubatorEggNotReadyToCatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Shared\Enums\IncubationStatus;
use Shared\Models\Incubation;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class CatchPokemonAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    private string $pokeballId;

    private Incubation $incubation;

    private Pokemon $pokemon;

    public function handle(User $user, IncubatorIndex $incubatorIndex, string $pokeballId): Pokemon
    {
        $this->user           = $user;
        $this->incubatorIndex = $incubatorIndex;
        $this->pokeballId     = $pokeballId;

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
            $this->markEggAsCaught();
            $this->addPokemonToUser();
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

    private function addPokemonToUser(): void
    {
        $this->pokemon = Pokemon::create([
            'user_id'          => $this->user->id,
            'original_user_id' => $this->user->id,
            'species_id'       => $this->incubation->species_id,
            'level'            => 1,
            'experience'       => 0,
            'in_party'         => false,
            'is_shiny'         => $this->incubation->is_shiny,
            'gender'           => $this->incubation->gender,
            'pokeball_id'      => $this->pokeballId,
            'caught_at'        => now(),
        ]);

        $baseMoveIds = $this->pokemon->species->learnableMoves()->baseMove()->take(4)->pluck('move_id');

        $this->pokemon->moves()->attach($baseMoveIds);
    }
}
