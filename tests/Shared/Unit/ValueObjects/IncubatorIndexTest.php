<?php

declare(strict_types=1);

use Shared\ValueObjects\FilterOption;
use Shared\ValueObjects\IncubatorIndex;

it('accepts an index within the allowed range', function () {
    $max = (int) config('game.max_incubators_per_user');

    expect((new IncubatorIndex(1))->value)->toBe(1)
        ->and((new IncubatorIndex($max))->value)->toBe($max);
});

it('rejects an index outside the allowed range', function (int $index) {
    expect(fn () => new IncubatorIndex($index))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'below one' => fn (): int => 0,
    'negative'  => fn (): int => -1,
    'above max' => fn (): int => (int) config('game.max_incubators_per_user') + 1,
]);

it('equals another index with the same value', function () {
    expect((new IncubatorIndex(1))->equals(new IncubatorIndex(1)))->toBeTrue();
});

it('does not equal an index with a different value', function () {
    expect((new IncubatorIndex(1))->equals(new IncubatorIndex(2)))->toBeFalse();
});

it('does not equal a different value object that shares the same underlying value', function () {
    expect((new IncubatorIndex(1))->equals(new FilterOption('One', 1)))->toBeFalse();
});
