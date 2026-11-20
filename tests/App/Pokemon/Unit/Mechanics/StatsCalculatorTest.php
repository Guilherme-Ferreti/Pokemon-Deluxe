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

it('doubles hp and attack and halves damage taken at boost 100', function () {
    $stats = new StatsCalculator()->calculate(level: 100, boost: 100, isShiny: false);

    expect($stats)->toEqual(new PokemonStats(hp: 800, attack: 110 / 77 * 2, defense: 0.5));
});

it('combines the boost and shine damage taken reductions', function () {
    $stats = new StatsCalculator()->calculate(level: 100, boost: 25, isShiny: true);

    expect($stats->defense)->toEqualWithDelta(0.6, 0.000001);
});

it('gives a shiny pokemon a quarter more hp and attack and a quarter less damage taken', function () {
    $stats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: true);

    expect($stats)->toEqual(new PokemonStats(hp: 500, attack: 110 / 77 * 1.25, defense: 0.75));
});

it('gives the stat bonuses as percentages', function (int $boost, bool $isShiny, float $expectedHpBonus, float $expectedDamageDoneBonus, float $expectedDamageTakenChange) {
    $calculator = new StatsCalculator;

    expect($calculator->hpBonusPercentage($boost, $isShiny))->toEqualWithDelta($expectedHpBonus, 0.000001)
        ->and($calculator->damageDoneBonusPercentage($boost, $isShiny))->toEqualWithDelta($expectedDamageDoneBonus, 0.000001)
        ->and($calculator->damageTakenChangePercentage($boost, $isShiny))->toEqualWithDelta($expectedDamageTakenChange, 0.000001);
})->with([
    'no boost, not shiny' => [0, false, 0.0, 0.0, 0.0],
    'boost 100'           => [100, false, 100.0, 100.0, -50.0],
    'shiny'               => [0, true, 25.0, 25.0, -25.0],
    'boost 100, shiny'    => [100, true, 150.0, 150.0, -62.5],
    'boost 20, shiny'     => [20, true, 50.0, 50.0, -37.5],
]);

it('rounds hp to the nearest whole number', function (int $boost, int $expectedHp) {
    $stats = new StatsCalculator()->calculate(level: 1, boost: $boost, isShiny: false);

    expect($stats->hp)->toBe($expectedHp);
})->with([
    'down from 40.4' => [1, 40],
    'up from 40.8'   => [2, 41],
]);
