<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Exploration\Mechanics\WildSpawnCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

final class SpeciesWildDataSeeder extends Seeder
{
    public function run(): void
    {
        $species = Species::query()->with('previousEvolutions')->get()->keyBy('id');

        $species->each(fn (Species $into) => $into->previousEvolutions->each(
            fn (SpeciesEvolution $evolution) => $evolution->setRelation('from', $species->get($evolution->from_species_id)),
        ));

        $calculator = new WildSpawnCalculator;

        $species
            ->map(function (Species $current) use ($calculator): array {
                $levelRange = $calculator->levelRange($current);

                return [
                    'evolution_stage' => $calculator->evolutionStage($current),
                    'wild_min_level'  => $levelRange[0] ?? null,
                    'wild_max_level'  => $levelRange[1] ?? null,
                ];
            })
            ->groupBy(fn (array $wildData): string => implode('-', $wildData), preserveKeys: true)
            ->each(fn (Collection $group) => Species::query()->whereKey($group->keys())->update($group->first()));
    }
}
