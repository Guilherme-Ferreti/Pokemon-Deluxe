<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
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
            'user_id'          => User::factory(),
            'original_user_id' => User::factory(),
            'species_id'       => Species::factory(),
            'level'            => fake()->numberBetween(1, 100),
            'experience'       => fake()->numberBetween(0, 100),
            'moves_ids'        => [],
            'in_party'         => fake()->boolean(),
            'is_shiny'         => fake()->boolean(10),
            'gender'           => function (array $attributes) {
                /** @var Species|null $species */
                $species = Species::find($attributes['species_id']);

                if (! $species || $species->isGenderless()) {
                    return PokemonGender::GENDERLESS;
                }

                return Lottery::odds($species->getMalePercentage(), 100)
                    ->winner(fn () => PokemonGender::MALE)
                    ->loser(fn () => PokemonGender::FEMALE)
                    ->choose();
            },
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

    public function regular(): static
    {
        return $this->state(['is_shiny' => false]);
    }

    public function genderless(): static
    {
        return $this->state(['gender' => PokemonGender::GENDERLESS]);
    }

    public function male(): static
    {
        return $this->state(['gender' => PokemonGender::MALE]);
    }

    public function female(): static
    {
        return $this->state(['gender' => PokemonGender::FEMALE]);
    }
}
