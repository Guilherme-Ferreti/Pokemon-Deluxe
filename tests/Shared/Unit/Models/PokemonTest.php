<?php

declare(strict_types=1);

use Shared\Models\Pokemon;

it('prefixes the species name with Shiny when the pokemon is shiny', function () {
    $pokemon = Pokemon::factory()->shiny()->create();

    expect($pokemon->getName())->toBe('Shiny ' . $pokemon->species->name);
});

it('returns the species name without the Shiny prefix when the pokemon is not shiny', function () {
    $pokemon = Pokemon::factory()->notShiny()->create();

    expect($pokemon->getName())->toBe($pokemon->species->name);
});

it('returns the front shiny sprite when the pokemon is shiny', function () {
    $pokemon = Pokemon::factory()->shiny()->create();

    expect($pokemon->getFrontSprite())->toBe($pokemon->species->sprites->front_shiny);
});

it('returns the front default sprite when the pokemon is not shiny', function () {
    $pokemon = Pokemon::factory()->notShiny()->create();

    expect($pokemon->getFrontSprite())->toBe($pokemon->species->sprites->front_default);
});
