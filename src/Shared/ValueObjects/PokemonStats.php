<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

final readonly class PokemonStats implements ValueObject
{
    public function __construct(
        public int $hp,
        public float $attack,
        public float $defense,
    ) {
        throw_if($hp < 1, InvalidArgumentException::class, "The hp must be at least 1, [{$hp}] given.");

        throw_if($attack < 0, InvalidArgumentException::class, "The attack cannot be negative, [{$attack}] given.");

        throw_if($defense < 0, InvalidArgumentException::class, "The defense cannot be negative, [{$defense}] given.");
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->hp === $other->hp
            && $this->attack === $other->attack
            && $this->defense === $other->defense;
    }
}
