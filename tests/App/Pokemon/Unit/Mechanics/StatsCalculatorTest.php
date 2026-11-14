<?php

declare(strict_types=1);

use App\Pokemon\Mechanics\StatsCalculator;
use Shared\ValueObjects\PokemonStats;

it('gives a level 1 pokemon without boost or shine the base stats', function () {
    $stats = new StatsCalculator()->calculate(level: 1, boost: 0, isShiny: false);

    expect($stats)->toEqual(new PokemonStats(hp: 40, attack: 11 / 77, defense: 1.0));
});

it('makes a level 100 pokemon ten times as strong as a level 1', function () {
    $stats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);

    expect($stats)->toEqual(new PokemonStats(hp: 400, attack: 110 / 77, defense: 1.0));
});

it('doubles hp and attack at boost 100', function () {
    $stats = new StatsCalculator()->calculate(level: 100, boost: 100, isShiny: false);

    expect($stats)->toEqual(new PokemonStats(hp: 800, attack: 110 / 77 * 2, defense: 1.0));
});

it('gives a shiny pokemon a quarter more hp and attack and a quarter less damage taken', function () {
    $stats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: true);

    expect($stats)->toEqual(new PokemonStats(hp: 500, attack: 110 / 77 * 1.25, defense: 0.75));
});

it('rounds hp to the nearest whole number', function (int $boost, int $expectedHp) {
    $stats = new StatsCalculator()->calculate(level: 1, boost: $boost, isShiny: false);

    expect($stats->hp)->toBe($expectedHp);
})->with([
    'down from 40.4' => [1, 40],
    'up from 40.8'   => [2, 41],
]);
