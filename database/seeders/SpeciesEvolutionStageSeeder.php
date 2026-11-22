<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Exploration\Mechanics\EvolutionStageCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

final class SpeciesEvolutionStageSeeder extends Seeder
{
    public function run(): void
    {
        $species = Species::query()->with('previousEvolutions')->get()->keyBy('id');

        $species->each(fn (Species $into) => $into->previousEvolutions->each(
            fn (SpeciesEvolution $evolution) => $evolution->setRelation('from', $species->get($evolution->from_species_id)),
        ));

        $calculator = new EvolutionStageCalculator;

        $species
            ->map(fn (Species $current): int => $calculator->evolutionStage($current))
            ->groupBy(fn (int $evolutionStage): int => $evolutionStage, preserveKeys: true)
            ->each(fn (Collection $group, int $evolutionStage) => Species::query()->whereKey($group->keys())->update(['evolution_stage' => $evolutionStage]));
    }
}
