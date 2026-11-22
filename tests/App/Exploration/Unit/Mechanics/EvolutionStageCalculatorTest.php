<?php

declare(strict_types=1);

use App\Exploration\Mechanics\EvolutionStageCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

it('places an unevolved pokemon at stage 1', function () {
    $charmander = Species::factory()->make(['name' => 'Charmander', 'is_baby' => false])
        ->setRelation('previousEvolutions', new EloquentCollection);

    expect(new EvolutionStageCalculator()->evolutionStage($charmander))->toBe(1);
});

it('places a pokemon one stage after its previous evolution', function () {
    $charmander = Species::factory()->make(['name' => 'Charmander', 'is_baby' => false])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $charmeleon = Species::factory()->make(['name' => 'Charmeleon', 'is_baby' => false])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 16])->setRelation('from', $charmander)]));
    $charizard = Species::factory()->make(['name' => 'Charizard', 'is_baby' => false])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 36])->setRelation('from', $charmeleon)]));

    expect(new EvolutionStageCalculator()->evolutionStage($charizard))->toBe(3);
});

it('does not count a baby previous evolution', function () {
    $pichu = Species::factory()->make(['name' => 'Pichu', 'is_baby' => true])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $pikachu = Species::factory()->make(['name' => 'Pikachu', 'is_baby' => false])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 15])->setRelation('from', $pichu)]));

    expect(new EvolutionStageCalculator()->evolutionStage($pikachu))->toBe(1);
});
