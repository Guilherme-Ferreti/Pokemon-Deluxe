<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Shared\Interfaces\ValueObject;

final class SpeciesPortraits implements ValueObject
{
    public function __construct(
        public ?string $regular,
        public ?string $shiny,
    ) {}

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->regular === $other->regular
            && $this->shiny === $other->shiny;
    }
}
