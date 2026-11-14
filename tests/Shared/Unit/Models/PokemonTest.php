<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\ValueObjects\PokemonStats;
use Shared\ValueObjects\SpeciesSprites;

describe('display name', function () {
    it('prefixes the species name with Shiny when the pokemon is shiny', function () {
        $pokemon = Pokemon::factory()->shiny()->create();

        expect($pokemon->getDisplayName())->toBe('Shiny ' . $pokemon->species->name);
    });

    it('returns the species name without the Shiny prefix when the pokemon is regular', function () {
        $pokemon = Pokemon::factory()->regular()->create();

        expect($pokemon->getDisplayName())->toBe($pokemon->species->name);
    });
});

describe('display front sprite', function () {
    it('returns the front shiny sprite when a male pokemon is shiny', function () {
        $shinyMale = Pokemon::factory()->shiny()->male()->create();

        expect($shinyMale->getDisplayFrontSprite())->toBe($shinyMale->species->sprites->front_shiny);
    });

    it('returns the front shiny sprite when a genderless pokemon is shiny', function () {
        $shinyGenderless = Pokemon::factory()->shiny()->genderless()->create();

        expect($shinyGenderless->getDisplayFrontSprite())->toBe($shinyGenderless->species->sprites->front_shiny);
    });

    it('returns the front shiny female sprite when a female pokemon is shiny', function () {
        $shinyFemale = Pokemon::factory()->shiny()->female()->create();

        expect($shinyFemale->getDisplayFrontSprite())->toBe($shinyFemale->species->sprites->front_shiny_female);
    });

    it('falls back to the front shiny sprite when a shiny female has no female sprite', function () {
        $species = Species::factory()->create([
            'sprites' => new SpeciesSprites(
                back_default: fake()->imageUrl(),
                back_female: fake()->imageUrl(),
                back_shiny: fake()->imageUrl(),
                back_shiny_female: fake()->imageUrl(),
                front_default: fake()->imageUrl(),
                front_female: fake()->imageUrl(),
                front_shiny: fake()->imageUrl(),
                front_shiny_female: null,
            ),
        ]);

        $shinyFemale = Pokemon::factory()->for($species)->shiny()->female()->create();

        expect($shinyFemale->getDisplayFrontSprite())->toBe($shinyFemale->species->sprites->front_shiny);
    });

    it('returns the front default sprite when a male pokemon is regular', function () {
        $male = Pokemon::factory()->regular()->male()->create();

        expect($male->getDisplayFrontSprite())->toBe($male->species->sprites->front_default);
    });

    it('returns the front default sprite when a genderless pokemon is regular', function () {
        $genderless = Pokemon::factory()->regular()->genderless()->create();

        expect($genderless->getDisplayFrontSprite())->toBe($genderless->species->sprites->front_default);
    });

    it('returns the front female sprite when a female pokemon is regular', function () {
        $female = Pokemon::factory()->regular()->female()->create();

        expect($female->getDisplayFrontSprite())->toBe($female->species->sprites->front_female);
    });

    it('falls back to the front default sprite when a regular female has no female sprite', function () {
        $species = Species::factory()->create([
            'sprites' => new SpeciesSprites(
                back_default: fake()->imageUrl(),
                back_female: fake()->imageUrl(),
                back_shiny: fake()->imageUrl(),
                back_shiny_female: fake()->imageUrl(),
                front_default: fake()->imageUrl(),
                front_female: null,
                front_shiny: fake()->imageUrl(),
                front_shiny_female: fake()->imageUrl(),
            ),
        ]);

        $female = Pokemon::factory()->for($species)->regular()->female()->create();

        expect($female->getDisplayFrontSprite())->toBe($female->species->sprites->front_default);
    });
});

describe('stats', function () {
    it('calculates the stats from its level, boost and shine', function () {
        $pokemon = Pokemon::factory()->boosted(100)->shiny()->create(['level' => 100]);

        expect($pokemon->getStats())->toEqual(new PokemonStats(hp: 1000, attack: 110 / 77 * 2 * 1.25, defense: 0.75));
    });
});
