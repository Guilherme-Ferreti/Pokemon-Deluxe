<?php

declare(strict_types=1);

use Shared\Models\Pokemon;

it('prefixes the pokedex name with Shiny when the pokemon is shiny', function () {
    $pokemon = Pokemon::factory()->shiny()->create();

    expect($pokemon->getName())->toBe('Shiny ' . $pokemon->pokedex->name);
});

it('returns the pokedex name without the Shiny prefix when the pokemon is not shiny', function () {
    $pokemon = Pokemon::factory()->notShiny()->create();

    expect($pokemon->getName())->toBe($pokemon->pokedex->name);
});

it('returns the front shiny sprite when the pokemon is shiny', function () {
    $pokemon = Pokemon::factory()->shiny()->create();

    expect($pokemon->getFrontSprite())->toBe($pokemon->pokedex->sprites->front_shiny);
});

it('returns the front default sprite when the pokemon is not shiny', function () {
    $pokemon = Pokemon::factory()->notShiny()->create();

    expect($pokemon->getFrontSprite())->toBe($pokemon->pokedex->sprites->front_default);
});
