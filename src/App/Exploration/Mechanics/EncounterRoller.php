<?php

declare(strict_types=1);

namespace App\Exploration\Mechanics;

use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Shared\Helpers\RandomHelper;
use Shared\Models\Species;

final class EncounterRoller
{
    /**
     * @param  Collection<int, Species>  $areaSpecies
     *
     * @throws InvalidArgumentException
     */
    public function roll(Collection $areaSpecies): NewPokemon
    {
        $species = $this->rollSpecies($areaSpecies);

        return new NewPokemon(
            species: $species,
            level: RandomHelper::integer((int) $species->wild_min_level, (int) $species->wild_max_level),
            is_shiny: RandomHelper::chance((float) config('game.pokemon.shiny_chance')),
            gender: $species->getRandomGender(),
        );
    }

    public function rollEvolutionStage(): int
    {
        /** @var array<int, int> $stageWeights */
        $stageWeights = config('game.exploration.stage_weights');

        $roll = RandomHelper::integer(1, (int) array_sum($stageWeights));

        foreach ($stageWeights as $stage => $weight) {
            $roll -= $weight;

            if ($roll <= 0) {
                return $stage;
            }
        }

        return array_key_last($stageWeights);
    }

    /**
     * @param  Collection<int, Species>  $areaSpecies
     */
    private function rollSpecies(Collection $areaSpecies): Species
    {
        [$legendarySpecies, $regularSpecies] = $areaSpecies->partition(
            fn (Species $species): bool => $species->is_legendary || $species->is_mythical,
        );

        if ($legendarySpecies->isNotEmpty() && ($regularSpecies->isEmpty() || RandomHelper::chance((float) config('game.exploration.legendary_chance')))) {
            return $this->pickSpecies($legendarySpecies);
        }

        return $this->pickSpecies($this->speciesAtRolledStage($regularSpecies));
    }

    /**
     * @param  Collection<int, Species>  $candidates
     *
     * @throws InvalidArgumentException
     */
    private function pickSpecies(Collection $candidates): Species
    {
        $species = $candidates->values()->all();

        if ($species === []) {
            throw new InvalidArgumentException('An encounter needs at least one species to pick from.');
        }

        return RandomHelper::pick($species);
    }

    /**
     * @param  Collection<int, Species>  $regularSpecies
     * @return Collection<int, Species>
     */
    private function speciesAtRolledStage(Collection $regularSpecies): Collection
    {
        $speciesByStage = $regularSpecies->groupBy('evolution_stage');

        for ($stage = $this->rollEvolutionStage(); $stage > 1; $stage--) {
            if ($speciesByStage->has($stage)) {
                return $speciesByStage->get($stage);
            }
        }

        return $speciesByStage->get(1, $regularSpecies);
    }
}
