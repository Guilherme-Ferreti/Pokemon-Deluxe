<?php

declare(strict_types=1);

use App\Battle\Mechanics\DamageCalculator;
use Shared\Enums\PokemonType;

it('returns the type multiplier of an attack against a single-type defender', function (PokemonType $defenderType, float $expectedMultiplier) {
    $multiplier = new DamageCalculator()->calculateDamageMultiplier(PokemonType::ELECTRIC, $defenderType);

    expect($multiplier)->toBe($expectedMultiplier);
})->with([
    'super effective'    => [PokemonType::WATER, 2.0],
    'neutral'            => [PokemonType::NORMAL, 1.0],
    'not very effective' => [PokemonType::GRASS, 0.5],
    'immune'             => [PokemonType::GROUND, 0.0],
]);

it('multiplies both type multipliers against a dual-type defender', function (PokemonType $secondDefenderType, float $expectedMultiplier) {
    $multiplier = new DamageCalculator()->calculateDamageMultiplier(PokemonType::ELECTRIC, PokemonType::WATER, $secondDefenderType);

    expect($multiplier)->toBe($expectedMultiplier);
})->with([
    'both super effective'         => [PokemonType::FLYING, 4.0],
    'super and not very effective' => [PokemonType::GRASS, 1.0],
    'one immune type'              => [PokemonType::GROUND, 0.0],
]);
