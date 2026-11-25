<?php

declare(strict_types=1);

use Shared\Helpers\RandomHelper;

describe('real randomness', function () {
    it('never succeeds at 0 percent and always succeeds at 100 percent', function () {
        $rolls = collect(range(1, 100));

        expect($rolls->map(fn () => RandomHelper::chance(0))->unique()->all())->toBe([false])
            ->and($rolls->map(fn () => RandomHelper::chance(100))->unique()->all())->toBe([true]);
    });

    it('rejects a chance outside 0 to 100 percent', function (float $percentage) {
        expect(fn () => RandomHelper::chance($percentage))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'below 0'   => -0.1,
        'above 100' => 100.1,
    ]);

    it('returns integers within the inclusive range', function () {
        $integers = collect(range(1, 100))->map(fn () => RandomHelper::integer(1, 3));

        expect($integers->min())->toBeGreaterThanOrEqual(1)
            ->and($integers->max())->toBeLessThanOrEqual(3);
    });

    it('returns the only integer of a one-value range', function () {
        expect(RandomHelper::integer(7, 7))->toBe(7);
    });

    it('returns floats within the range', function () {
        $floats = collect(range(1, 100))->map(fn () => RandomHelper::float(0.85, 1.0));

        expect($floats->min())->toBeGreaterThanOrEqual(0.85)
            ->and($floats->max())->toBeLessThanOrEqual(1.0);
    });

    it('picks one of the given items', function () {
        $items = ['bulbasaur' => 'Bulbasaur', 'charmander' => 'Charmander', 'squirtle' => 'Squirtle'];

        $picks = collect(range(1, 100))->map(fn () => RandomHelper::pick($items));

        expect($picks->diff($items)->all())->toBeEmpty();
    });

    it('picks the given number of different items', function () {
        $items = ['Bulbasaur', 'Charmander', 'Squirtle', 'Pikachu', 'Eevee'];

        $picks = RandomHelper::pickMany($items, 3);

        expect($picks)->toHaveCount(3)
            ->and(array_unique($picks))->toHaveCount(3)
            ->and(array_diff($picks, $items))->toBeEmpty();
    });

    it('refuses to pick more items than it was given', function () {
        RandomHelper::pickMany(['Bulbasaur', 'Charmander'], 3);
    })->throws(InvalidArgumentException::class, 'Cannot pick 3 of 2 items.');
});

describe('fake', function () {
    it('returns the faked values in order whatever method is called', function () {
        RandomHelper::fake([true, 42, 0.5, 'Pikachu']);

        expect(RandomHelper::chance(0))->toBeTrue()
            ->and(RandomHelper::integer(1, 3))->toBe(42)
            ->and(RandomHelper::float(0.85, 1.0))->toBe(0.5)
            ->and(RandomHelper::pick(['Eevee']))->toBe('Pikachu');
    });

    it('throws when the faked values run out', function () {
        RandomHelper::fake([true]);

        RandomHelper::chance(50);

        expect(fn () => RandomHelper::chance(50))
            ->toThrow(LogicException::class, 'RandomHelper ran out of faked values.');
    });

    it('still rejects a chance outside 0 to 100 percent', function () {
        RandomHelper::fake([true]);

        expect(fn () => RandomHelper::chance(101))
            ->toThrow(InvalidArgumentException::class);
    });

    it('goes back to real randomness after a reset', function () {
        RandomHelper::fake([]);

        RandomHelper::reset();

        expect(RandomHelper::integer(7, 7))->toBe(7);
    });
});
