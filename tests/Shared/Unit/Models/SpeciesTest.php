<?php

declare(strict_types=1);

use Shared\Enums\PokemonGender;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesSprites;

describe('catch rate', function () {
    it('returns 100 when catch rate is 255', function () {
        $species = Species::factory()->create(['catch_rate' => 255]);

        expect($species->getCatchRatePercentage())->toBe(100);
    });

    it('returns 0 when catch rate is 0', function () {
        $species = Species::factory()->create(['catch_rate' => 0]);

        expect($species->getCatchRatePercentage())->toBe(0);
    });

    it('calculates the catch rate percentage correctly', function () {
        $species = Species::factory()->create(['catch_rate' => 127]);

        expect($species->getCatchRatePercentage())->toBe(49.8);
    });
});

describe('back sprite', function () {
    it('picks the back sprite for the gender and shine', function (PokemonGender $gender, bool $isShiny, string $expectedSprite) {
        $species = Species::factory()->create(['sprites' => new SpeciesSprites(
            back_default: 'back-default.png',
            back_female: 'back-female.png',
            back_shiny: 'back-shiny.png',
            back_shiny_female: 'back-shiny-female.png',
            front_default: 'front-default.png',
            front_female: 'front-female.png',
            front_shiny: 'front-shiny.png',
            front_shiny_female: 'front-shiny-female.png',
        )]);

        expect($species->getDisplayBackSprite($gender, $isShiny))->toBe($expectedSprite);
    })->with([
        'male'         => [PokemonGender::MALE, false, 'back-default.png'],
        'female'       => [PokemonGender::FEMALE, false, 'back-female.png'],
        'shiny male'   => [PokemonGender::MALE, true, 'back-shiny.png'],
        'shiny female' => [PokemonGender::FEMALE, true, 'back-shiny-female.png'],
    ]);

    it('falls back to the front sprite when the species has no back sprite', function () {
        $species = Species::factory()->create(['sprites' => new SpeciesSprites(
            back_default: null,
            back_female: null,
            back_shiny: null,
            back_shiny_female: null,
            front_default: 'front-default.png',
            front_female: null,
            front_shiny: 'front-shiny.png',
            front_shiny_female: null,
        )]);

        expect($species->getDisplayBackSprite(PokemonGender::MALE, shiny: true))->toBe('front-shiny.png');
    });
});
