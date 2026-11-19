<?php

declare(strict_types=1);

use Shared\ValueObjects\FilterOption;
use Shared\ValueObjects\IncubatorIndex;

describe('value', function () {
    it('accepts an index within the allowed range', function () {
        $max = (int) config('game.user.max_incubators');

        expect(fn () => new IncubatorIndex(1))->not->toThrow(InvalidArgumentException::class)
            ->and(fn () => new IncubatorIndex($max))->not->toThrow(InvalidArgumentException::class);
    });

    it('rejects an index outside the allowed range', function (int $index) {
        expect(fn () => new IncubatorIndex($index))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'below one' => fn () => 0,
        'negative'  => fn () => -1,
        'above max' => fn () => (int) config('game.user.max_incubators') + 1,
    ]);
});

describe('equality', function () {
    it('equals another index with the same value', function () {
        expect(new IncubatorIndex(1)->equals(new IncubatorIndex(1)))->toBeTrue();
    });

    it('does not equal an index with a different value', function () {
        expect(new IncubatorIndex(1)->equals(new IncubatorIndex(2)))->toBeFalse();
    });

    it('does not equal a different value object that shares the same underlying value', function () {
        expect(new IncubatorIndex(1)->equals(new FilterOption('One', 1)))->toBeFalse();
    });
});
