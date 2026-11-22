<?php

declare(strict_types=1);

use Database\Seeders\EvolutionItemSeeder;
use Database\Seeders\SpeciesEvolutionStageSeeder;
use Database\Seeders\SpeciesSeeder;
use Shared\Models\Species;

use function Pest\Laravel\seed;

it('assigns each species a gapless pokedex order', function () {
    seed([EvolutionItemSeeder::class, SpeciesSeeder::class]);

    $orders = Species::query()->orderBy('pokedex_order')->pluck('pokedex_order');

    expect($orders->all())->toBe(range(1, $orders->count()));
});

it('orders species by pokedex number, with regular forms before mega forms before gmax forms', function () {
    seed([EvolutionItemSeeder::class, SpeciesSeeder::class]);

    $slugs = Species::query()
        ->orderBy('pokedex_order')
        ->whereIn('slug', ['charmeleon', 'charizard', 'mega-charizard-x', 'mega-charizard-y', 'gmax-charizard', 'squirtle'])
        ->pluck('slug');

    expect($slugs->all())->toBe(['charmeleon', 'charizard', 'mega-charizard-x', 'mega-charizard-y', 'gmax-charizard', 'squirtle']);
});

it('marks exactly the eleven ultra beasts', function () {
    seed([EvolutionItemSeeder::class, SpeciesSeeder::class]);

    $slugs = Species::query()->where('is_ultra_beast', true)->orderBy('pokedex_order')->pluck('slug');

    expect($slugs->all())->toBe(['nihilego', 'buzzwole', 'pheromosa', 'xurkitree', 'celesteela', 'kartana', 'guzzlord', 'poipole', 'naganadel', 'stakataka', 'blacephalon']);
});

it('saves each species wild levels from the data files', function () {
    seed([EvolutionItemSeeder::class, SpeciesSeeder::class]);

    $species = Species::query()
        ->whereIn('slug', ['charmander', 'charizard', 'raichu', 'mewtwo', 'pichu', 'mega-charizard-x'])
        ->get(['slug', 'wild_min_level', 'wild_max_level'])
        ->keyBy('slug')
        ->map(fn (Species $species) => [$species->wild_min_level, $species->wild_max_level]);

    expect($species->all())->toEqual([
        'charmander'       => [2, 12],
        'charizard'        => [36, 40],
        'raichu'           => [18, 22],
        'mewtwo'           => [50, 70],
        'pichu'            => [null, null],
        'mega-charizard-x' => [null, null],
    ]);
});

it('saves each species evolution stage', function () {
    seed([EvolutionItemSeeder::class, SpeciesSeeder::class, SpeciesEvolutionStageSeeder::class]);

    $species = Species::query()
        ->whereIn('slug', ['charmander', 'charizard', 'pikachu', 'raichu', 'mega-charizard-x'])
        ->pluck('evolution_stage', 'slug');

    expect($species->all())->toEqual([
        'charmander'       => 1,
        'charizard'        => 3,
        'pikachu'          => 1,
        'raichu'           => 2,
        'mega-charizard-x' => 4,
    ]);
});
