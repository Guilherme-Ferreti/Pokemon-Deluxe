<?php

declare(strict_types=1);

namespace Shared\Helpers;

use Closure;
use InvalidArgumentException;
use LogicException;
use Random\IntervalBoundary;
use Random\Randomizer;

final class RandomHelper
{
    /**
     * @var (Closure(): mixed)|null
     */
    private static ?Closure $nextFakedValue = null;

    /**
     * @throws InvalidArgumentException
     */
    public static function chance(float $percentage): bool
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidArgumentException("A chance must be a percentage between 0 and 100, [{$percentage}] given.");
        }

        if (self::$nextFakedValue instanceof Closure) {
            return (self::$nextFakedValue)();
        }

        return new Randomizer()->nextFloat() * 100 < $percentage;
    }

    public static function integer(int $min, int $max): int
    {
        if (self::$nextFakedValue instanceof Closure) {
            return (self::$nextFakedValue)();
        }

        return new Randomizer()->getInt($min, $max);
    }

    public static function float(float $min, float $max): float
    {
        if (self::$nextFakedValue instanceof Closure) {
            return (self::$nextFakedValue)();
        }

        return new Randomizer()->getFloat($min, $max, IntervalBoundary::ClosedClosed);
    }

    /**
     * @template TItem
     *
     * @param  non-empty-array<array-key, TItem>  $items
     * @return TItem
     */
    public static function pick(array $items): mixed
    {
        if (self::$nextFakedValue instanceof Closure) {
            return (self::$nextFakedValue)();
        }

        return $items[new Randomizer()->pickArrayKeys($items, 1)[0]];
    }

    /**
     * Makes every following call return the next of the given values, in order, whatever method is called.
     * A call after the values run out throws, so a test that rolls more often than expected fails loudly.
     *
     * @param  list<mixed>  $values
     */
    public static function fake(array $values): void
    {
        self::$nextFakedValue = static function () use (&$values): mixed {
            if ($values === []) {
                throw new LogicException('RandomHelper ran out of faked values.');
            }

            return array_shift($values);
        };
    }

    public static function reset(): void
    {
        self::$nextFakedValue = null;
    }
}
