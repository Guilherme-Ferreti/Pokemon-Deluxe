<?php

declare(strict_types=1);

use App\Dex\Enums\SearchPokedexSortBy;
use App\Dex\Filters\SearchPokedexFilters;
use App\Dex\Queries\SearchPokedexQuery;
use Shared\Enums\Generation;
use Shared\Enums\PokemonType;
use Shared\Enums\SortDirection;
use Shared\Enums\SpeciesTrait;
use Shared\Models\Species;

it('returns all species when no filters are applied', function () {
    Species::factory()->count(3)->create();

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(3);
});

it('filters species by matching name', function () {
    $pikachu    = Species::factory()->create(['name' => 'Pikachu']);
    $charmander = Species::factory()->create(['name' => 'Charmander']);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: 'Pikachu',
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($pikachu->id);
});

it('filters species by partial name match', function () {
    $pikachu    = Species::factory()->create(['name' => 'Pikachu']);
    $raichu     = Species::factory()->create(['name' => 'Raichu']);
    $charmander = Species::factory()->create(['name' => 'Charmander']);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: 'chu',
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($pikachu->id, $raichu->id)->not->toContain($charmander->id);
});

it('returns no results when the search matches nothing', function () {
    Species::factory()->create(['name' => 'Pikachu']);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: 'Missing',
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toBeEmpty();
});

it('filters species by a single type', function () {
    $fireOnly  = Species::factory()->create(['types' => [PokemonType::FIRE]]);
    $waterOnly = Species::factory()->create(['types' => [PokemonType::WATER]]);
    $fireWater = Species::factory()->create(['types' => [PokemonType::FIRE, PokemonType::WATER]]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [PokemonType::FIRE],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($fireOnly->id, $fireWater->id)->not->toContain($waterOnly->id);
});

it('requires both types when two types are selected', function () {
    $fireOnly  = Species::factory()->create(['types' => [PokemonType::FIRE]]);
    $fireWater = Species::factory()->create(['types' => [PokemonType::FIRE, PokemonType::WATER]]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [PokemonType::FIRE, PokemonType::WATER],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($fireWater->id);
});

it('filters species by traits', function (SpeciesTrait $trait, ?string $column) {
    $species = Species::factory()->create([$column => true]);

    Species::factory(5)->create([$column => false]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [$trait],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($species->id);
})->with([
    'baby'      => [SpeciesTrait::BABY, 'is_baby'],
    'legendary' => [SpeciesTrait::LEGENDARY, 'is_legendary'],
    'mythical'  => [SpeciesTrait::MYTHICAL, 'is_mythical'],
    'mega'      => [SpeciesTrait::MEGA, 'is_mega'],
    'gmax'      => [SpeciesTrait::GMAX, 'is_gmax'],
]);

it('requires all selected traits to match', function () {
    $legendaryBaby = Species::factory()->create(['is_legendary' => true, 'is_baby' => true]);
    $legendaryOnly = Species::factory()->create(['is_legendary' => true, 'is_baby' => false]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [SpeciesTrait::LEGENDARY, SpeciesTrait::BABY],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($legendaryBaby->id);
});

it('filters species by a single generation', function () {
    $genI  = Species::factory()->create(['generation' => Generation::I]);
    $genII = Species::factory()->create(['generation' => Generation::II]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [Generation::I],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($genI->id);
});

it('filters species by multiple generations', function () {
    $genI   = Species::factory()->create(['generation' => Generation::I]);
    $genII  = Species::factory()->create(['generation' => Generation::II]);
    $genIII = Species::factory()->create(['generation' => Generation::III]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [Generation::I, Generation::II],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(2)
        ->and($result->pluck('id'))->toContain($genI->id, $genII->id)->not->toContain($genIII->id);
});

it('sorts species by pokedex number with mega and gmax ordered after regular forms', function () {
    $regular = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => false]);
    $mega    = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);
    $gmax    = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => true]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result->pluck('id')->all())->toBe([$regular->id, $mega->id, $gmax->id]);
});

it('ignores sort direction for pokedex number', function () {
    $low  = Species::factory()->create(['pokedex_number' => 1]);
    $mid  = Species::factory()->create(['pokedex_number' => 100]);
    $high = Species::factory()->create(['pokedex_number' => 700]);

    $ascResult = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();
    $descResult = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::DESC,
    ))->get();

    expect($ascResult->pluck('id')->all())->toBe([$low->id, $mid->id, $high->id])
        ->and($descResult->pluck('id')->all())->toBe([$low->id, $mid->id, $high->id]);
});

it('sorts species by name ascending', function () {
    $charmander = Species::factory()->create(['name' => 'Charmander']);
    $pikachu    = Species::factory()->create(['name' => 'Pikachu']);
    $squirtle   = Species::factory()->create(['name' => 'Squirtle']);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::NAME,
        dir: SortDirection::ASC,
    ))->get();

    expect($result->pluck('id')->all())->toBe([$charmander->id, $pikachu->id, $squirtle->id]);
});

it('sorts species by name descending', function () {
    $charmander = Species::factory()->create(['name' => 'Charmander']);
    $pikachu    = Species::factory()->create(['name' => 'Pikachu']);
    $squirtle   = Species::factory()->create(['name' => 'Squirtle']);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: null,
        types: [],
        traits: [],
        generations: [],
        sort: SearchPokedexSortBy::NAME,
        dir: SortDirection::DESC,
    ))->get();

    expect($result->pluck('id')->all())->toBe([$squirtle->id, $pikachu->id, $charmander->id]);
});

it('combines multiple filters together', function () {
    $match = Species::factory()->create([
        'name'         => 'Charizard',
        'types'        => [PokemonType::FIRE, PokemonType::FLYING],
        'generation'   => Generation::I,
        'is_legendary' => false,
    ]);

    Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC], 'generation' => Generation::I]);
    Species::factory()->create(['name' => 'Charmander', 'types' => [PokemonType::FIRE], 'generation' => Generation::I]);
    Species::factory()->create(['name' => 'Charizard', 'types' => [PokemonType::FIRE, PokemonType::FLYING], 'generation' => Generation::VI]);

    $result = new SearchPokedexQuery()->handle(new SearchPokedexFilters(
        search: 'Charizard',
        types: [PokemonType::FIRE, PokemonType::FLYING],
        traits: [],
        generations: [Generation::I],
        sort: SearchPokedexSortBy::POKEDEX_NUMBER,
        dir: SortDirection::ASC,
    ))->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($match->id);
});
