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
            'level_learn'  => fake()->randomElement([null, fake()->numberBetween(1, config('game.pokemon.max_level'))]),
            'is_base_move' => fake()->boolean(),
        ];
    }

    public function byLevelUp(?int $level = null): static
    {
        return $this->state(fn () => [
            'level_learn' => $level ?? fake()->numberBetween(1, config('game.pokemon.max_level')),
        ]);
    }

    public function byTM(): static
    {
        return $this->state(fn () => [
            'level_learn' => null,
        ]);
    }
}
