<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\EggIncubator;

use App\Pokemon\Exceptions\EggIncubatorNotIdleException;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\Species;
use Shared\Models\User;

final class PlaceEggAction
{
    private User $user;

    private int $eggIncubatorIndex;

    public function handle(User $user, int $eggIncubatorIndex): void
    {
        $this->user              = $user;
        $this->eggIncubatorIndex = $eggIncubatorIndex;

        $this->ensureEggIncubatorIsIdle();

        $this->createEggIncubation();
    }

    private function ensureEggIncubatorIsIdle(): void
    {
        $availability = new GetEggIncubatorsAvailabilityAction()->handle($this->user);

        if (in_array($this->eggIncubatorIndex, $availability['idle'])) {
            return;
        }

        throw new EggIncubatorNotIdleException($this->eggIncubatorIndex);
    }

    private function createEggIncubation(): void
    {
        $species = $this->getRandomEggObtainableSpecies();

        $this->user->eggIncubations()->create([
            'egg_incubator_index' => $this->eggIncubatorIndex,
            'species_id'          => $species->id,
            'gender'              => $this->rollGender($species),
            'is_shiny'            => $this->rollIsShiny(),
            'ready_to_hatch_at'   => now()->addDay(),
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
