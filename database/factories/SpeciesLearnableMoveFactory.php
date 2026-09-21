<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\SpeciesLearnableMove;

/**
 * @extends Factory<SpeciesLearnableMove>
 */
final class SpeciesLearnableMoveFactory extends Factory
{
    protected $model = SpeciesLearnableMove::class;

    public function definition(): array
    {
        return [
            'species_id'   => Species::factory(),
            'move_id'      => Move::factory(),
            'level_learn'  => fake()->numberBetween(1, 100),
            'is_base_move' => false,
        ];
    }

    public function learnedByTM(): static
    {
        return $this->state(fn (): array => ['level_learn' => null]);
    }
}
