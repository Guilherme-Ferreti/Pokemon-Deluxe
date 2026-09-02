<?php

declare(strict_types=1);

use App\Dex\Queries\PreviousSpeciesQuery;
use Shared\Models\Species;

it('returns the previous species by pokedex number', function () {
    $previous = Species::factory()->create(['pokedex_number' => 1]);
    $current  = Species::factory()->create(['pokedex_number' => 2]);

    $result = (new PreviousSpeciesQuery)->handle($current);

    expect($result->id)->toBe($previous->id);
});

it('returns the correct previous species across different pokedex numbers', function () {
    $first  = Species::factory()->create(['pokedex_number' => 1]);
    $second = Species::factory()->create(['pokedex_number' => 4]);
    $third  = Species::factory()->create(['pokedex_number' => 7]);
    $fourth = Species::factory()->create(['pokedex_number' => 25]);

    $result = (new PreviousSpeciesQuery)->handle($fourth);

    expect($result->id)->toBe($third->id);
});

it('returns null when there is not a previous species', function () {
    $current = Species::factory()->create(['pokedex_number' => 25]);
    $second  = Species::factory()->create(['pokedex_number' => 26]);

    $result = (new PreviousSpeciesQuery)->handle($current);

    expect($result)->toBeNull();
});

it('returns the previous species as regular before mega for the same pokedex number', function () {
    $regular = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => false]);
    $mega    = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);

    $result = (new PreviousSpeciesQuery)->handle($mega);

    expect($result->id)->toBe($regular->id);
});

it('returns the previous species as mega before gmax for the same pokedex number', function () {
    $mega = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);
    $gmax = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => true]);

    $result = (new PreviousSpeciesQuery)->handle($gmax);

    expect($result->id)->toBe($mega->id);
});

it('returns the previous species for a regular species with many forms', function () {
    $previous = Species::factory()->create(['pokedex_number' => 24, 'is_mega' => false, 'is_gmax' => false]);
    $regular  = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => false]);
    $mega     = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => true, 'is_gmax' => false]);
    $gmax     = Species::factory()->create(['pokedex_number' => 25, 'is_mega' => false, 'is_gmax' => true]);

    $result = (new PreviousSpeciesQuery)->handle($regular);

    expect($result->id)->toBe($previous->id);
});
