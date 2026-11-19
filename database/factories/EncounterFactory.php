<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Override;
use Shared\Enums\Area;
use Shared\Models\Encounter;
use Shared\Models\Species;
use Shared\Models\User;

/**
 * @extends Factory<Encounter>
 */
final class EncounterFactory extends Factory
{
    #[Override]
    protected $model = Encounter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'area'       => fake()->randomElement(Area::cases()),
            'species_id' => Species::factory(),
            'level'      => fake()->numberBetween(2, 12),
            'is_shiny'   => fake()->boolean(1),
            'gender'     => fn (array $attributes) => Species::findOrFail((string) $attributes['species_id'])->getRandomGender(),
        ];
    }
}
