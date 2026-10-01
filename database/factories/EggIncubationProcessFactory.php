<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\EggIncubationProcess;
use Shared\Models\Species;
use Shared\Models\User;

/**
 * @extends Factory<EggIncubationProcess>
 */
final class EggIncubationProcessFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'egg_incubator_index'   => fn (array $attributes) => $this->resolveEggIncubatorIndex($attributes['user_id']),
            'species_id'            => Species::factory(),
            'gender'                => fn (array $attributes) => $this->resolveGender($attributes['species_id']),
            'is_shiny'              => fake()->boolean(10),
            'egg_ready_to_hatch_at' => fake()->dateTimeBetween('-2 year', '-1 year'),
            'egg_hatched_at'        => fake()->dateTimeBetween('-11 months', '-2 months'),
            'egg_captured_at'       => fake()->dateTimeBetween('-1 month', 'now'),
            'user_id'               => User::factory(),
        ];
    }

    private function resolveEggIncubatorIndex(string $userId): int
    {
        /** @var User $user */
        $user = User::find($userId);

        return fake()->randomElement($user->unlocked_egg_incubator_indexes);
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
            'egg_ready_to_hatch_at' => $until ?? now()->addDays(1),
            'egg_hatched_at'        => null,
            'egg_captured_at'       => null,
        ]);
    }

    public function readyToHatch(): static
    {
        return $this->state([
            'egg_ready_to_hatch_at' => now(),
            'egg_hatched_at'        => null,
            'egg_captured_at'       => null,
        ]);
    }

    public function readyToCapture(): static
    {
        return $this->state([
            'egg_ready_to_hatch_at' => now()->subMinute(),
            'egg_hatched_at'        => now(),
            'egg_captured_at'       => null,
        ]);
    }
}
