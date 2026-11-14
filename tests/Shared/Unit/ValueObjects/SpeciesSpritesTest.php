<?php

declare(strict_types=1);

use Shared\ValueObjects\SpeciesSprites;

describe('equality', function () {
    it('equals another sprites with same values', function () {
        $sprites = new SpeciesSprites(
            back_default: null,
            back_female: null,
            back_shiny: null,
            back_shiny_female: null,
            front_default: 'front.png',
            front_female: null,
            front_shiny: null,
            front_shiny_female: null,
        );

        $sameSprites = new SpeciesSprites(
            back_default: null,
            back_female: null,
            back_shiny: null,
            back_shiny_female: null,
            front_default: 'front.png',
            front_female: null,
            front_shiny: null,
            front_shiny_female: null,
        );

        expect($sprites->equals($sameSprites))->toBeTrue();
    });

    it('does not equal sprites when any field differs', function (string $field, string $differentValue) {
        $values = [
            'back_default'       => null,
            'back_female'        => null,
            'back_shiny'         => null,
            'back_shiny_female'  => null,
            'front_default'      => 'front.png',
            'front_female'       => null,
            'front_shiny'        => null,
            'front_shiny_female' => null,
        ];

        $sprites          = new SpeciesSprites(...$values);
        $differentSprites = new SpeciesSprites(...[...$values, $field => $differentValue]);

        expect($sprites->equals($differentSprites))->toBeFalse();
    })->with([
        'back_default different'       => ['back_default', 'back.png'],
        'back_female different'        => ['back_female', 'bf.png'],
        'back_shiny different'         => ['back_shiny', 'bs.png'],
        'back_shiny_female different'  => ['back_shiny_female', 'bsf.png'],
        'front_default different'      => ['front_default', 'front2.png'],
        'front_female different'       => ['front_female', 'ff.png'],
        'front_shiny different'        => ['front_shiny', 'fs.png'],
        'front_shiny_female different' => ['front_shiny_female', 'fsf.png'],
    ]);
});
