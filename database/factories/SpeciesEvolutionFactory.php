<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Override;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

/**
 * @extends Factory<SpeciesEvolution>
 */
final class SpeciesEvolutionFactory extends Factory
{
    #[Override]
    protected $model = SpeciesEvolution::class;

    public function definition(): array
    {
        return [
            'from_species_id'   => Species::factory(),
            'into_species_id'   => Species::factory(),
            'evolution_item_id' => fake()->optional()->passthrough(EvolutionItem::factory()),
            'min_level'         => fake()->optional()->numberBetween(10, 60),
            'gender'            => fake()->optional()->randomElement(PokemonGender::cases()),
        ];
    }
}
