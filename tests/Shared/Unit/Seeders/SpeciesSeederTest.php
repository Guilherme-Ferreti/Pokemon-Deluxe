<?php

declare(strict_types=1);

use Database\Seeders\EvolutionItemSeeder;
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
