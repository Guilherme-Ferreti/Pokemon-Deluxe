<?php

declare(strict_types=1);

use App\Pokemon\Exceptions\EvolutionNotOfSpeciesException;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

it('says the pokemon cannot evolve into that species', function () {
    $pokemon   = Pokemon::factory()->for(Species::factory()->state(['name' => 'Charmander']))->shiny()->create();
    $evolution = SpeciesEvolution::factory()->for(Species::factory()->state(['name' => 'Ivysaur']), 'into')->create();

    expect(new EvolutionNotOfSpeciesException($pokemon, $evolution)->getMessage())
        ->toBe("Shiny Charmander can't evolve into Shiny Ivysaur.");
});
