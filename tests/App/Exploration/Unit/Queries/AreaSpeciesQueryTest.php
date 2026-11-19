<?php

declare(strict_types=1);

use App\Exploration\Queries\AreaSpeciesQuery;
use Shared\Enums\Area;
use Shared\Enums\PokemonType;
use Shared\Models\Species;

it('returns the species with one of the area types in pokedex order', function () {
    $butterfree = Species::factory()->create(['types' => [PokemonType::BUG, PokemonType::FLYING], 'pokedex_order' => 120]);
    $bulbasaur  = Species::factory()->create(['types' => [PokemonType::GRASS, PokemonType::POISON], 'pokedex_order' => 10]);
    $caterpie   = Species::factory()->create(['types' => [PokemonType::BUG], 'pokedex_order' => 100]);

    $result = new AreaSpeciesQuery()->handle(Area::FOREST)->get();

    expect($result->pluck('id')->all())->toBe([$bulbasaur->id, $caterpie->id, $butterfree->id]);
});

it('leaves out species without any of the area types', function () {
    Species::factory()->create(['types' => [PokemonType::FIRE]]);
    Species::factory()->create(['types' => [PokemonType::WATER, PokemonType::FLYING]]);

    $result = new AreaSpeciesQuery()->handle(Area::FOREST)->get();

    expect($result)->toBeEmpty();
});

it('leaves out species that never appear in the wild', function (string $flag) {
    Species::factory()->create([
        'types'          => [PokemonType::GRASS],
        $flag            => true,
        'wild_min_level' => null,
        'wild_max_level' => null,
    ]);

    $result = new AreaSpeciesQuery()->handle(Area::FOREST)->get();

    expect($result)->toBeEmpty();
})->with(['is_mega', 'is_gmax', 'is_baby']);
