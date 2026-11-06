<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Models\Incubation;
use Shared\Models\Species;
use Shared\Models\User;

/**
 * @extends Factory<Incubation>
 */
final class IncubationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'species_id'        => Species::factory(),
            'incubator_index'   => fn (array $attributes) => fake()->randomElement(User::findOrFail((string) $attributes['user_id'])->purchased_incubator_indexes),
            'gender'            => fn (array $attributes) => Species::findOrFail((string) $attributes['species_id'])->getRandomGender(),
            'is_shiny'          => fake()->boolean(10),
            'ready_to_hatch_at' => fake()->dateTimeBetween('-2 years', '-1 year'),
            'hatched_at'        => fake()->dateTimeBetween('-11 months', '-2 months'),
            'caught_at'         => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function incubating(?CarbonImmutable $until = null): static
    {
        return $this->state([
            'ready_to_hatch_at' => $until ?? now()->addDays(1),
            'hatched_at'        => null,
            'caught_at'         => null,
        ]);
    }

    public function readyToHatch(): static
    {
        return $this->state([
            'ready_to_hatch_at' => now(),
            'hatched_at'        => null,
            'caught_at'         => null,
        ]);
    }

    public function readyToCatch(): static
    {
        return $this->state([
            'ready_to_hatch_at' => now()->subMinute(),
            'hatched_at'        => now(),
            'caught_at'         => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'ready_to_hatch_at' => fake()->dateTimeBetween('-2 years', '-1 year'),
            'hatched_at'        => fake()->dateTimeBetween('-11 months', '-2 months'),
            'caught_at'         => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
