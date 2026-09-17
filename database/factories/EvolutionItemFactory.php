<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Models\EvolutionItem;

/**
 * @extends Factory<EvolutionItem>
 */
final class EvolutionItemFactory extends Factory
{
    protected $model = EvolutionItem::class;

    public function definition(): array
    {
        return [
            'slug'        => fake()->unique()->uuid(),
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'sprite'      => fake()->optional()->imageUrl(),
        ];
    }
}
