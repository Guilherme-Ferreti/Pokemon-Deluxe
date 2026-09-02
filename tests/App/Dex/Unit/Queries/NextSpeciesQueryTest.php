<?php

declare(strict_types=1);

use App\Dex\Queries\NextSpeciesQuery;
use Shared\Models\Species;

it('returns the next species by pokedex number', function () {
    $current = Species::factory()->create(['pokedex_number' => 1]);
    $next    = Species::factory()->create(['pokedex_number' => 2]);

    $result = (new NextSpeciesQuery)->handle($current);

    expect($result->id)->toBe($next->id);
});

it('returns the correct next species across different pokedex numbers', function () {
    $first  = Species::factory()->create(['pokedex_number' => 1]);
    $second = Species::factory()->create(['pokedex_number' => 4]);
    $third  = Species::factory()->create(['pokedex_number' => 7]);
    $fourth = Species::factory()->create(['pokedex_number' => 25]);

    $result = (new NextSpeciesQuery)->handle($first);

    expect($result->id)->toBe($second->id);
});

it('returns null when there is not a next species', function () {
    $first   = Species::factory()->create(['pokedex_number' => 24]);
    $current = Species::factory()->create(['pokedex_number' => 25]);

    $result = (new NextSpeciesQuery)->handle($current);

    expect($result)->toBeNull();
});

it('returns the next species as mega after regular for the same pokedex number', function () {
    $regular = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => false]);
    $mega    = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);

    $result = (new NextSpeciesQuery)->handle($regular);

    expect($result->id)->toBe($mega->id);
});

it('returns gmax as next after mega for the same pokedex number', function () {
    $mega = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);
    $gmax = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => true]);

    $result = (new NextSpeciesQuery)->handle($mega);

    expect($result->id)->toBe($gmax->id);
});

it('returns the next species for a gmax species with many forms', function () {
    $regular = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => false]);
    $mega    = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);
    $gmax    = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => true]);
    $next    = Species::factory()->create(['pokedex_number' => 26]);

    $result = (new NextSpeciesQuery)->handle($gmax);

    expect($result->id)->toBe($next->id);
});
