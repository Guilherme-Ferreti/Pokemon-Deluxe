<?php

declare(strict_types=1);

use App\Exploration\Mechanics\WildSpawnCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

it('places an unevolved pokemon at stage 1 and levels 2 to 12', function () {
    $charmander = Species::factory()->make(['name' => 'Charmander', 'is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false])
        ->setRelation('previousEvolutions', new EloquentCollection);

    $calculator = new WildSpawnCalculator;

    expect($calculator->evolutionStage($charmander))->toBe(1)
        ->and($calculator->levelRange($charmander))->toBe([2, 12]);
});

it('places a pokemon evolved by level at its evolution level to 4 above', function () {
    $regular = ['is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false];

    $charmander = Species::factory()->make(['name' => 'Charmander', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $charmeleon = Species::factory()->make(['name' => 'Charmeleon', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 16])->setRelation('from', $charmander)]));
    $charizard = Species::factory()->make(['name' => 'Charizard', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 36])->setRelation('from', $charmeleon)]));

    $calculator = new WildSpawnCalculator;

    expect($calculator->evolutionStage($charizard))->toBe(3)
        ->and($calculator->levelRange($charizard))->toBe([36, 40]);
});

it('places a pokemon evolved another way at levels 18 to 22', function () {
    $regular = ['is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false];

    $pikachu = Species::factory()->make(['name' => 'Pikachu', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $raichu = Species::factory()->make(['name' => 'Raichu', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => null])->setRelation('from', $pikachu)]));

    $calculator = new WildSpawnCalculator;

    expect($calculator->evolutionStage($raichu))->toBe(2)
        ->and($calculator->levelRange($raichu))->toBe([18, 22]);
});

it('never places a pokemon evolved another way below the previous stage minimum', function () {
    $regular = ['is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false];

    $oddish = Species::factory()->make(['name' => 'Oddish', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $gloom = Species::factory()->make(['name' => 'Gloom', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 21])->setRelation('from', $oddish)]));
    $vileplume = Species::factory()->make(['name' => 'Vileplume', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => null])->setRelation('from', $gloom)]));

    $calculator = new WildSpawnCalculator;

    expect($calculator->levelRange($gloom))->toBe([21, 25])
        ->and($calculator->levelRange($vileplume))->toBe([21, 25]);
});

it('does not count a baby previous evolution', function () {
    $pichu = Species::factory()->make(['name' => 'Pichu', 'is_baby' => true, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $pikachu = Species::factory()->make(['name' => 'Pikachu', 'is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 15])->setRelation('from', $pichu)]));

    $calculator = new WildSpawnCalculator;

    expect($calculator->evolutionStage($pikachu))->toBe(1)
        ->and($calculator->levelRange($pikachu))->toBe([2, 12]);
});

it('never places a pokemon above the max level', function () {
    $regular = ['is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false];

    $floette = Species::factory()->make(['name' => 'Floette', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $eternalFloette = Species::factory()->make(['name' => 'Eternal Floette', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([new SpeciesEvolution()->forceFill(['min_level' => 98])->setRelation('from', $floette)]));

    expect(new WildSpawnCalculator()->levelRange($eternalFloette))->toBe([98, 100]);
});

it('uses the lowest range when a pokemon evolves from its previous stage in several ways', function () {
    $regular = ['is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false];

    $eevee = Species::factory()->make(['name' => 'Eevee', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection);
    $evolved = Species::factory()->make(['name' => 'Evolved', ...$regular])
        ->setRelation('previousEvolutions', new EloquentCollection([
            new SpeciesEvolution()->forceFill(['min_level' => 30])->setRelation('from', $eevee),
            new SpeciesEvolution()->forceFill(['min_level' => null])->setRelation('from', $eevee),
        ]));

    expect(new WildSpawnCalculator()->levelRange($evolved))->toBe([18, 22]);
});

it('places a legendary or mythical pokemon at levels 50 to 70', function (string $trait) {
    $mewtwo = Species::factory()->make(['name' => 'Mewtwo', 'is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false, $trait => true])
        ->setRelation('previousEvolutions', new EloquentCollection);

    expect(new WildSpawnCalculator()->levelRange($mewtwo))->toBe([50, 70]);
})->with([
    'legendary' => ['is_legendary'],
    'mythical'  => ['is_mythical'],
]);

it('never places a baby, mega or gmax pokemon in the wild', function (string $trait) {
    $species = Species::factory()->make(['is_baby' => false, 'is_legendary' => false, 'is_mythical' => false, 'is_mega' => false, 'is_gmax' => false, $trait => true])
        ->setRelation('previousEvolutions', new EloquentCollection);

    expect(new WildSpawnCalculator()->levelRange($species))->toBeNull();
})->with([
    'baby (Pichu)'            => ['is_baby'],
    'mega (Mega Charizard X)' => ['is_mega'],
    'gmax (G-Max Pikachu)'    => ['is_gmax'],
]);
