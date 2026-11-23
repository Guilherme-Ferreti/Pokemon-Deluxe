<?php

declare(strict_types=1);

use Shared\ValueObjects\SpeciesSprites;

describe('equality', function () {
    it('equals another sprites with same values', function () {
        $sprites = new SpeciesSprites(
            backDefault: null,
            backFemale: null,
            backShiny: null,
            backShinyFemale: null,
            frontDefault: 'front.png',
            frontFemale: null,
            frontShiny: null,
            frontShinyFemale: null,
        );

        $sameSprites = new SpeciesSprites(
            backDefault: null,
            backFemale: null,
            backShiny: null,
            backShinyFemale: null,
            frontDefault: 'front.png',
            frontFemale: null,
            frontShiny: null,
            frontShinyFemale: null,
        );

        expect($sprites->equals($sameSprites))->toBeTrue();
    });

    it('does not equal sprites when any field differs', function (string $field, string $differentValue) {
        $values = [
            'backDefault'      => null,
            'backFemale'       => null,
            'backShiny'        => null,
            'backShinyFemale'  => null,
            'frontDefault'     => 'front.png',
            'frontFemale'      => null,
            'frontShiny'       => null,
            'frontShinyFemale' => null,
        ];

        $sprites          = new SpeciesSprites(...$values);
        $differentSprites = new SpeciesSprites(...[...$values, $field => $differentValue]);

        expect($sprites->equals($differentSprites))->toBeFalse();
    })->with([
        'backDefault different'      => ['backDefault', 'back.png'],
        'backFemale different'       => ['backFemale', 'bf.png'],
        'backShiny different'        => ['backShiny', 'bs.png'],
        'backShinyFemale different'  => ['backShinyFemale', 'bsf.png'],
        'frontDefault different'     => ['frontDefault', 'front2.png'],
        'frontFemale different'      => ['frontFemale', 'ff.png'],
        'frontShiny different'       => ['frontShiny', 'fs.png'],
        'frontShinyFemale different' => ['frontShinyFemale', 'fsf.png'],
    ]);
});
