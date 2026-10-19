<?php

declare(strict_types=1);

use Shared\ValueObjects\SpeciesPortraits;

it('equals another portraits with same values', function () {
    expect((new SpeciesPortraits('regular.png', 'shiny.png'))->equals(new SpeciesPortraits('regular.png', 'shiny.png')))->toBeTrue();
});

it('does not equal portraits with different regular', function () {
    expect((new SpeciesPortraits('regular.png', 'shiny.png'))->equals(new SpeciesPortraits('regular2.png', 'shiny.png')))->toBeFalse();
});

it('does not equal portraits with different shiny', function () {
    expect((new SpeciesPortraits('regular.png', 'shiny.png'))->equals(new SpeciesPortraits('regular.png', 'shiny2.png')))->toBeFalse();
});

it('treats null as equal', function () {
    expect((new SpeciesPortraits(null, null))->equals(new SpeciesPortraits(null, null)))->toBeTrue();
});
