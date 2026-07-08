<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Models\Pokedex;
use Shared\ValueObjects\PokemonSprites;

/**
 * @extends Factory<Pokedex>
 */
final class PokedexFactory extends Factory
{
    protected $model = Pokedex::class;

    public function definition(): array
    {
        return [
            'name'                      => fake()->unique()->word(),
            'primary_type'              => fake()->randomElement(ElementalType::cases()),
            'secondary_type'            => fake()->optional()->randomElement(ElementalType::cases()),
            'national_dex_entry_number' => fake()->unique()->numberBetween(1, 1000),
            'genus'                     => fake()->word(),
            'text_entry'                => fake()->sentence(),
            'sprites'                   => new PokemonSprites(
                back_default: fake()->optional()->imageUrl(),
                back_female: fake()->optional()->imageUrl(),
                back_shiny: fake()->optional()->imageUrl(),
                back_shiny_female: fake()->optional()->imageUrl(),
                front_default: fake()->optional()->imageUrl(),
                front_female: fake()->optional()->imageUrl(),
                front_shiny: fake()->optional()->imageUrl(),
                front_shiny_female: fake()->optional()->imageUrl(),
            ),
            'is_baby'      => fake()->boolean(5),
            'is_legendary' => fake()->boolean(5),
            'is_mythical'  => fake()->boolean(2),
            'is_mega'      => fake()->boolean(5),
            'is_gmax'      => fake()->boolean(5),
            'generation'   => fake()->randomElement(Generation::cases()),
            'capture_rate' => fake()->optional()->numberBetween(3, 255),
            'internal_id'  => fake()->unique()->uuid(),
            'external_id'  => fake()->unique()->uuid(),
        ];
    }
}
