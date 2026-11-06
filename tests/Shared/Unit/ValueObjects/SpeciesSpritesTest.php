<?php

declare(strict_types=1);

use Shared\ValueObjects\SpeciesSprites;

/**
 * @param  array<string, string|null>  $overrides
 */
function makeSpeciesSprites(array $overrides = []): SpeciesSprites
{
    return new SpeciesSprites(...[
        'back_default'       => $overrides['back_default'] ?? null,
        'back_female'        => $overrides['back_female'] ?? null,
        'back_shiny'         => $overrides['back_shiny'] ?? null,
        'back_shiny_female'  => $overrides['back_shiny_female'] ?? null,
        'front_default'      => $overrides['front_default'] ?? null,
        'front_female'       => $overrides['front_female'] ?? null,
        'front_shiny'        => $overrides['front_shiny'] ?? null,
        'front_shiny_female' => $overrides['front_shiny_female'] ?? null,
    ]);
}
describe('equality', function () {
    it('equals another sprites with same values', function () {
        expect(makeSpeciesSprites(['front_default' => 'front.png'])->equals(makeSpeciesSprites(['front_default' => 'front.png'])))->toBeTrue();
    });

    it('does not equal sprites when any field differs', function (array $diff) {
        expect(makeSpeciesSprites(['front_default' => 'front.png'])->equals(makeSpeciesSprites($diff)))->toBeFalse();
    })->with([
        'back_default different'       => fn () => ['front_default' => 'front.png', 'back_default' => 'back.png'],
        'back_female different'        => fn () => ['front_default' => 'front.png', 'back_female' => 'bf.png'],
        'back_shiny different'         => fn () => ['front_default' => 'front.png', 'back_shiny' => 'bs.png'],
        'back_shiny_female different'  => fn () => ['front_default' => 'front.png', 'back_shiny_female' => 'bsf.png'],
        'front_default different'      => fn () => ['front_default' => 'front2.png'],
        'front_female different'       => fn () => ['front_default' => 'front.png', 'front_female' => 'ff.png'],
        'front_shiny different'        => fn () => ['front_default' => 'front.png', 'front_shiny' => 'fs.png'],
        'front_shiny_female different' => fn () => ['front_default' => 'front.png', 'front_shiny_female' => 'fsf.png'],
    ]);
});
