<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

final class IncubatorIndex implements ValueObject
{
    /**
     * @var positive-int
     */
    public int $value;

    public function __construct(int $value)
    {
        $max = (int) config('game.max_incubators_per_user');

        throw_if($value < 1 || $value > $max, InvalidArgumentException::class, "The incubator index must be between 1 and {$max}.");

        $this->value = $value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
