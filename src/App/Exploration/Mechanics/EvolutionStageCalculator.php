<?php

declare(strict_types=1);

namespace App\Exploration\Mechanics;

use Illuminate\Support\Collection;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

final class EvolutionStageCalculator
{
    public function evolutionStage(Species $species): int
    {
        $previousStages = $this->nonBabyPreviousEvolutions($species)
            ->map(fn (SpeciesEvolution $evolution): int => $this->evolutionStage($evolution->from));

        return $previousStages->isEmpty() ? 1 : 1 + (int) $previousStages->min();
    }

    /**
     * @return Collection<int, SpeciesEvolution>
     */
    private function nonBabyPreviousEvolutions(Species $species): Collection
    {
        return $species->previousEvolutions
            ->toBase()
            ->reject(fn (SpeciesEvolution $evolution): bool => $evolution->from->is_baby)
            ->values();
    }
}
