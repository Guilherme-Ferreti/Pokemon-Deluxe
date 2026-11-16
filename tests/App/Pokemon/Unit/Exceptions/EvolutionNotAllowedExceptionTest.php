<?php

declare(strict_types=1);

use App\Pokemon\Enums\EvolutionRequirement;
use App\Pokemon\Exceptions\EvolutionNotAllowedException;
use Shared\Models\SpeciesEvolution;

it('says the pokemon cannot evolve yet, with the reason', function () {
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => null, 'min_level' => 36, 'gender' => null])->load('evolutionItem');

    expect(new EvolutionNotAllowedException(EvolutionRequirement::LEVEL, $evolution)->getMessage())
        ->toBe("This Pokémon can't evolve yet. Needs level 36.");
});
