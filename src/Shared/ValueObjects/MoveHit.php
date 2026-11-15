<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

final readonly class MoveHit implements ValueObject
{
    public function __construct(
        public bool $isCriticalHit,
        public float $randomFactor,
    ) {
        throw_if($randomFactor <= 0 || $randomFactor > 1, InvalidArgumentException::class, "The random factor must be above 0 and at most 1, [{$randomFactor}] given.");
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->isCriticalHit === $other->isCriticalHit
            && $this->randomFactor === $other->randomFactor;
    }
}
