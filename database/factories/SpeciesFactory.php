<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesPortraits;
use Shared\ValueObjects\SpeciesSprites;

/**
 * @extends Factory<Species>
 */
final class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        return [
            'name'  => fake()->unique()->word(),
            'types' => array_filter([
                fake()->randomElement(ElementalType::cases()),
                fake()->optional()->randomElement(ElementalType::cases()),
            ]),
            'pokedex_number' => fake()->unique()->numberBetween(1, 1000),
            'genus'          => fake()->word(),
            'text_entry'     => fake()->sentence(),
            'sprites'        => new SpeciesSprites(
                back_default: fake()->imageUrl(width: 96, height: 96, word: 'back default'),
                back_female: fake()->imageUrl(width: 96, height: 96, word: 'back female'),
                back_shiny: fake()->imageUrl(width: 96, height: 96, word: 'back shiny'),
                back_shiny_female: fake()->imageUrl(width: 96, height: 96, word: 'back shiny female'),
                front_default: fake()->imageUrl(width: 96, height: 96, word: 'front default'),
                front_female: fake()->imageUrl(width: 96, height: 96, word: 'front female'),
                front_shiny: fake()->imageUrl(width: 96, height: 96, word: 'front shiny'),
                front_shiny_female: fake()->imageUrl(width: 96, height: 96, word: 'front shiny female'),
            ),
            'portraits' => new SpeciesPortraits(
                regular: fake()->imageUrl(width: 96, height: 96, word: 'regular'),
                shiny: fake()->imageUrl(width: 96, height: 96, word: 'shiny'),
            ),
            'is_baby'      => fake()->boolean(5),
            'is_legendary' => fake()->boolean(5),
            'is_mythical'  => fake()->boolean(2),
            'is_mega'      => fake()->boolean(5),
            'is_gmax'      => fake()->boolean(5),
            'generation'   => fake()->randomElement(Generation::cases()),
            'capture_rate' => fake()->numberBetween(3, 255),
            'slug'         => fake()->unique()->uuid(),
            'external_id'  => fake()->unique()->uuid(),
        ];
    }
}
