<?php

declare(strict_types=1);

namespace App\Exploration\Mechanics;

use Illuminate\Support\Collection;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

final class WildSpawnCalculator
{
    public function evolutionStage(Species $species): int
    {
        $previousStages = $this->nonBabyPreviousEvolutions($species)
            ->map(fn (SpeciesEvolution $evolution): int => $this->evolutionStage($evolution->from));

        return $previousStages->isEmpty() ? 1 : 1 + (int) $previousStages->min();
    }

    /**
     * @return array{int, int}|null
     */
    public function levelRange(Species $species): ?array
    {
        if ($species->is_mega || $species->is_gmax || $species->is_baby) {
            return null;
        }

        if ($species->is_legendary || $species->is_mythical) {
            return $this->configuredRange('game.exploration.legendary_levels');
        }

        if ($this->evolutionStage($species) === 1) {
            return $this->configuredRange('game.exploration.unevolved_levels');
        }

        return $this->nonBabyPreviousEvolutions($species)
            ->map(fn (SpeciesEvolution $evolution): array => $this->levelRangeThrough($evolution))
            ->sort()
            ->first();
    }

    /**
     * @return array{int, int}
     */
    private function levelRangeThrough(SpeciesEvolution $evolution): array
    {
        $minLevel = $evolution->min_level ?? max(
            (int) config('game.exploration.other_evolution_min_level'),
            $this->levelRange($evolution->from)[0] ?? 0,
        );

        $maxLevel = min(
            $minLevel + (int) config('game.exploration.evolved_level_spread'),
            (int) config('game.pokemon.max_level'),
        );

        return [$minLevel, $maxLevel];
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

    /**
     * @return array{int, int}
     */
    private function configuredRange(string $key): array
    {
        [$minLevel, $maxLevel] = config($key);

        return [(int) $minLevel, (int) $maxLevel];
    }
}
