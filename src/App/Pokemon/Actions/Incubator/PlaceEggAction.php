<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Exceptions\IncubatorNotIdleException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class PlaceEggAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    public function handle(User $user, IncubatorIndex $incubatorIndex): void
    {
        $this->user           = $user;
        $this->incubatorIndex = $incubatorIndex;

        Cache::withoutOverlapping(
            key: LockKeys::incubatorPlaceEgg($user->id, $incubatorIndex),
            callback: fn () => $this->placeEgg()
        );
    }

    private function placeEgg(): void
    {
        $this->ensureIncubatorIsIdle();

        $this->createIncubation();
    }

    private function ensureIncubatorIsIdle(): void
    {
        $availability = new GetIncubatorsAvailabilityAction()->handle($this->user);

        if (value_object_array_contains($availability['idle'], $this->incubatorIndex)) {
            return;
        }

        throw new IncubatorNotIdleException($this->incubatorIndex);
    }

    private function createIncubation(): void
    {
        $species = $this->getRandomEggObtainableSpecies();

        $this->user->Incubations()->create([
            'incubator_index'   => $this->incubatorIndex,
            'species_id'        => $species->id,
            'gender'            => $this->rollGender($species),
            'is_shiny'          => $this->rollIsShiny(),
            'ready_to_hatch_at' => now()->addDay(),
        ]);
    }

    private function getRandomEggObtainableSpecies(): Species
    {
        return Species::query()
            ->where('egg_obtainable', true)
            ->inRandomOrder()
            ->firstOrFail();
    }

    private function rollGender(Species $species): PokemonGender
    {
        if ($species->isGenderless()) {
            return PokemonGender::GENDERLESS;
        }

        return Lottery::odds($species->getMalePercentage(), 100)
            ->winner(fn () => PokemonGender::MALE)
            ->loser(fn () => PokemonGender::FEMALE)
            ->choose();
    }

    private function rollIsShiny(): bool
    {
        return Lottery::odds(1, 100)
            ->winner(fn () => true)
            ->loser(fn () => false)
            ->choose();
    }
}
