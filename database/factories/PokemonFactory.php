<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Models\Pokeball;
use Shared\Models\Pokedex;
use Shared\Models\Pokemon;
use Shared\Models\User;

/**
 * @extends Factory<Pokemon>
 */
final class PokemonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'pokedex_id'  => Pokedex::factory(),
            'level'       => fake()->numberBetween(1, 100),
            'experience'  => fake()->numberBetween(0, 100),
            'moves_ids'   => [],
            'in_party'    => fake()->boolean(),
            'is_shiny'    => fake()->boolean(10),
            'pokeball_id' => Pokeball::factory(),
            'captured_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function inParty(): static
    {
        return $this->state(['in_party' => true]);
    }

    public function inBox(): static
    {
        return $this->state(['in_party' => false]);
    }

    public function shiny(): static
    {
        return $this->state(['is_shiny' => true]);
    }

    public function notShiny(): static
    {
        return $this->state(['is_shiny' => false]);
    }
}
