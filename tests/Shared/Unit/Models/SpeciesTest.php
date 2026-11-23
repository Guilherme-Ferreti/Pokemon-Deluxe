<?php

declare(strict_types=1);

use Shared\Enums\PokemonGender;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesSprites;

describe('catch rate', function () {
    it('returns 100 when catch rate is 255', function () {
        $species = Species::factory()->create(['catch_rate' => 255]);

        expect($species->getDisplayCatchRatePercentage())->toBe(100);
    });

    it('returns 0 when catch rate is 0', function () {
        $species = Species::factory()->create(['catch_rate' => 0]);

        expect($species->getDisplayCatchRatePercentage())->toBe(0);
    });

    it('calculates the catch rate percentage correctly', function () {
        $species = Species::factory()->create(['catch_rate' => 127]);

        expect($species->getDisplayCatchRatePercentage())->toBe(49.8);
    });
});

describe('back sprite', function () {
    it('picks the back sprite for the gender and shine', function (PokemonGender $gender, bool $isShiny, string $expectedSprite) {
        $species = Species::factory()->create(['sprites' => new SpeciesSprites(
            backDefault: 'back-default.png',
            backFemale: 'back-female.png',
            backShiny: 'back-shiny.png',
            backShinyFemale: 'back-shiny-female.png',
            frontDefault: 'front-default.png',
            frontFemale: 'front-female.png',
            frontShiny: 'front-shiny.png',
            frontShinyFemale: 'front-shiny-female.png',
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
            backDefault: null,
            backFemale: null,
            backShiny: null,
            backShinyFemale: null,
            frontDefault: 'front-default.png',
            frontFemale: null,
            frontShiny: 'front-shiny.png',
            frontShinyFemale: null,
        )]);

        expect($species->getDisplayBackSprite(PokemonGender::MALE, shiny: true))->toBe('front-shiny.png');
    });
});
