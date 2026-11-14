<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Pokemon\Services\ExperienceCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\PokemonGender;
use Shared\Models\Move;
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
            'level'            => fake()->numberBetween(1, (int) config('game.pokemon.max_level')),
            'experience'       => fn (array $attributes) => new ExperienceCalculator()->experienceForLevel((int) $attributes['level']),
            'boost'            => fake()->numberBetween(0, (int) config('game.pokemon.max_boost')),
            'in_party'         => fake()->boolean(),
            'is_shiny'         => fake()->boolean(10),
            'gender'           => fn (array $attributes) => Species::findOrFail((string) $attributes['species_id'])->getRandomGender(),
            'pokeball_id'      => Pokeball::factory(),
            'caught_at'        => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function boosted(int $boost): static
    {
        return $this->state(['boost' => $boost]);
    }

    public function withActiveMoves(int $count = 4): static
    {
        return $this->afterCreating(fn (Pokemon $pokemon) => $pokemon->knownMoves()->attach(
            Move::factory()->count($count)->create()->values()->mapWithKeys(fn (Move $move, int $index) => [$move->id => ['slot' => $index + 1]])->all(),
        ));
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
