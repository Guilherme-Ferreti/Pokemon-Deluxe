<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

final class EggIncubatorIndex implements Castable, ValueObject
{
    /**
     * @var positive-int
     */
    public int $value;

    public function __construct(int $value)
    {
        $max = (int) config('game.max_egg_incubators_per_user');

        if ($value < 1 || $value > $max) {
            throw new InvalidArgumentException("The egg incubator index must be between 1 and {$max}.");
        }

        $this->value = $value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return CastsAttributes<EggIncubatorIndex, int>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, $value, array $attributes): EggIncubatorIndex
            {
                return new EggIncubatorIndex((int) $value);
            }

            /**
             * @return positive-int
             */
            public function set(Model $model, string $key, $value, array $attributes): int
            {
                if (! $value instanceof EggIncubatorIndex) {
                    throw new InvalidArgumentException('The value must be an instance of ' . EggIncubatorIndex::class . '.');
                }

                return $value->value;
            }
        };
    }
}
