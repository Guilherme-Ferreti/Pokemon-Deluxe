<?php

declare(strict_types=1);

use Shared\ValueObjects\IncubatorIndex;
use Shared\ValueObjects\PokemonStats;

it('accepts 1 hp and zero attack and defense', function () {
    expect(fn () => new PokemonStats(1, 0.0, 0.0))->not->toThrow(InvalidArgumentException::class);
});

it('rejects a negative stat', function (int $hp, float $attack, float $defense, string $message) {
    expect(fn () => new PokemonStats($hp, $attack, $defense))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'zero hp' => [0, 0.5, 1.0, 'The hp must be at least 1, [0] given.'],
    'attack'  => [44, -0.1, 1.0, 'The attack cannot be negative, [-0.1] given.'],
    'defense' => [44, 0.5, -0.75, 'The defense cannot be negative, [-0.75] given.'],
]);

it('equals stats with the same hp, attack and defense', function () {
    expect(new PokemonStats(44, 0.5, 1.0)->equals(new PokemonStats(44, 0.5, 1.0)))->toBeTrue();
});

it('does not equal stats that differ in any value', function (PokemonStats $other) {
    expect(new PokemonStats(44, 0.5, 1.0)->equals($other))->toBeFalse();
})->with([
    'hp'      => fn () => new PokemonStats(45, 0.5, 1.0),
    'attack'  => fn () => new PokemonStats(44, 0.6, 1.0),
    'defense' => fn () => new PokemonStats(44, 0.5, 0.75),
]);

it('does not equal a different value object', function () {
    expect(new PokemonStats(44, 0.5, 1.0)->equals(new IncubatorIndex(1)))->toBeFalse();
});
