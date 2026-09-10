<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\MoveCategory;
use Shared\Models\Move;

/**
 * @extends Factory<Move>
 */
final class MoveFactory extends Factory
{
    protected $model = Move::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'           => fake()->unique()->word(),
            'type'           => fake()->randomElement(ElementalType::cases()),
            'category'       => fake()->randomElement(MoveCategory::cases()),
            'damage_class'   => fn (array $attributes) => fake()->randomElement($attributes['category']->possibleDamageClasses()),
            'power'          => fn (array $attributes) => $attributes['category'] === MoveCategory::AILMENT ? null : fake()->optional()->numberBetween(10, 250),
            'accuracy'       => fake()->numberBetween(30, 100),
            'pp'             => fake()->numberBetween(1, 40),
            'ailment'        => fn (array $attributes) => in_array($attributes['category'], [MoveCategory::DAMAGE, MoveCategory::OHKO]) ? null : fake()->optional()->randomElement(Ailment::cases()),
            'ailment_chance' => fn (array $attributes) => is_null($attributes['ailment']) ? null : fake()->optional()->numberBetween(0, 100),
            'flinch_chance'  => fake()->optional()->numberBetween(0, 100),
            'min_hits'       => fake()->optional()->numberBetween(1, 6),
            'max_hits'       => fake()->optional()->numberBetween(1, 6),
            'is_zmove'       => fake()->boolean(),
            'generation'     => fake()->randomElement(Generation::cases()),
            'slug'           => fake()->unique()->slug(),
            'external_id'    => fake()->unique()->slug(),
        ];
    }
}
