<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\PokeballType;
use Shared\Models\Pokeball;

/**
 * @extends Factory<Pokeball>
 */
final class PokeballFactory extends Factory
{
    protected $model = Pokeball::class;

    public function definition(): array
    {
        return [
            'slug'         => fake()->unique()->uuid(),
            'name'         => fake()->unique()->word(),
            'description'  => fake()->sentence(),
            'sprite'       => fake()->optional()->imageUrl(),
            'type'         => fake()->randomElement(PokeballType::cases()),
            'capture_rate' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
