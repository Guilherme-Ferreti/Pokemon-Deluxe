<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Override;
use Shared\Enums\PokeballType;
use Shared\Models\Pokeball;

/**
 * @extends Factory<Pokeball>
 */
final class PokeballFactory extends Factory
{
    #[Override]
    protected $model = Pokeball::class;

    public function definition(): array
    {
        return [
            'slug'        => fake()->unique()->uuid(),
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'sprite'      => fake()->optional()->imageUrl(),
            'type'        => fake()->randomElement(PokeballType::cases()),
            'catch_rate'  => fake()->randomFloat(2, 0, 100),
            'price'       => fake()->numberBetween(1, 50) * 100,
        ];
    }
}
