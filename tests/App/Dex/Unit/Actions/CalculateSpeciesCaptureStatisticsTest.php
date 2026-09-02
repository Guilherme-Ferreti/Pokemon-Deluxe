<?php

declare(strict_types=1);

use App\Dex\Actions\CalculateSpeciesCaptureStatistics;
use Shared\Models\Pokemon;
use Shared\Models\Species;

it('counts regular and shiny captures for the species', function () {
    $species = Species::factory()->create();

    Pokemon::factory()->for($species)->count(2)->regular()->create();
    Pokemon::factory()->for($species)->count(1)->shiny()->create();

    $statistics = new CalculateSpeciesCaptureStatistics()->handle($species);

    expect($statistics)->toBe(['regular' => 2, 'shiny' => 1]);
});

it('returns zero counts when the species has no captures', function () {
    $species = Species::factory()->create();

    $statistics = new CalculateSpeciesCaptureStatistics()->handle($species);

    expect($statistics)->toBe(['regular' => 0, 'shiny' => 0]);
});

it('counts only the captures of the given species', function () {
    $speciesA = Species::factory()->create();
    $speciesB = Species::factory()->create();

    Pokemon::factory()->for($speciesA)->count(1)->regular()->create();
    Pokemon::factory()->for($speciesB)->count(3)->regular()->create();

    $statistics = new CalculateSpeciesCaptureStatistics()->handle($speciesA);

    expect($statistics)->toBe(['regular' => 1, 'shiny' => 0]);
});
