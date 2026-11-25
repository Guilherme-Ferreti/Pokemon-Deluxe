<?php

declare(strict_types=1);

namespace App\Pokemon\ValueObjects;

use Shared\Interfaces\ValueObject;

final readonly class BoostResult implements ValueObject
{
    public function __construct(
        public int $stonesUsed,
        public int $successes,
        public int $failures,
        public int $boostBefore,
        public int $boostAfter,
    ) {}

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->stonesUsed === $other->stonesUsed
            && $this->successes === $other->successes
            && $this->failures === $other->failures
            && $this->boostBefore === $other->boostBefore
            && $this->boostAfter === $other->boostAfter;
    }
}
