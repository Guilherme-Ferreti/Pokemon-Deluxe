<?php

declare(strict_types=1);

use Database\Seeders\EvolutionItemSeeder;
use Database\Seeders\SpeciesSeeder;
use Database\Seeders\SpeciesWildDataSeeder;
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

it('saves each species evolution stage and wild levels', function () {
    seed([EvolutionItemSeeder::class, SpeciesSeeder::class, SpeciesWildDataSeeder::class]);

    $species = Species::query()
        ->whereIn('slug', ['charmander', 'charizard', 'pikachu', 'raichu', 'mewtwo', 'pichu', 'mega-charizard-x'])
        ->get(['slug', 'evolution_stage', 'wild_min_level', 'wild_max_level'])
        ->keyBy('slug')
        ->map(fn (Species $species) => [$species->evolution_stage, $species->wild_min_level, $species->wild_max_level]);

    expect($species->all())->toEqual([
        'charmander'       => [1, 2, 12],
        'charizard'        => [3, 36, 40],
        'pikachu'          => [1, 2, 12],
        'raichu'           => [2, 18, 22],
        'mewtwo'           => [1, 50, 70],
        'pichu'            => [1, null, null],
        'mega-charizard-x' => [4, null, null],
    ]);
});
