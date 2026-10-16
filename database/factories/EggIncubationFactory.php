<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\EggIncubation;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

/**
 * @extends Factory<EggIncubation>
 */
final class EggIncubationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'species_id'          => Species::factory(),
            'egg_incubator_index' => fn (array $attributes) => $this->resolveEggIncubatorIndex($attributes['user_id']),
            'gender'              => fn (array $attributes) => $this->resolveGender($attributes['species_id']),
            'is_shiny'            => fake()->boolean(10),
            'ready_to_hatch_at'   => fake()->dateTimeBetween('-2 years', '-1 year'),
            'hatched_at'          => fake()->dateTimeBetween('-11 months', '-2 months'),
            'captured_at'         => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    private function resolveEggIncubatorIndex(string $userId): EggIncubatorIndex
    {
        /** @var User $user */
        $user = User::find($userId);

        return fake()->randomElement($user->purchased_egg_incubator_indexes);
    }

    private function resolveGender(string $speciesId): PokemonGender
    {
        /** @var Species $species */
        $species = Species::find($speciesId);

        if ($species->isGenderless()) {
            return PokemonGender::GENDERLESS;
        }

        return Lottery::odds($species->getMalePercentage(), 100)
            ->winner(fn () => PokemonGender::MALE)
            ->loser(fn () => PokemonGender::FEMALE)
            ->choose();
    }

    public function incubating(?CarbonImmutable $until = null): static
    {
        return $this->state([
            'ready_to_hatch_at' => $until ?? now()->addDays(1),
            'hatched_at'        => null,
            'captured_at'       => null,
        ]);
    }

    public function readyToHatch(): static
    {
        return $this->state([
            'ready_to_hatch_at' => now(),
            'hatched_at'        => null,
            'captured_at'       => null,
        ]);
    }

    public function readyToCapture(): static
    {
        return $this->state([
            'ready_to_hatch_at' => now()->subMinute(),
            'hatched_at'        => now(),
            'captured_at'       => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'ready_to_hatch_at' => fake()->dateTimeBetween('-2 years', '-1 year'),
            'hatched_at'        => fake()->dateTimeBetween('-11 months', '-2 months'),
            'captured_at'       => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
