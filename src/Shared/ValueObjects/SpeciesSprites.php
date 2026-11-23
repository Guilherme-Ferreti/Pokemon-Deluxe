<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Shared\Interfaces\ValueObject;

final readonly class SpeciesSprites implements ValueObject
{
    public function __construct(
        public ?string $backDefault,
        public ?string $backFemale,
        public ?string $backShiny,
        public ?string $backShinyFemale,
        public ?string $frontDefault,
        public ?string $frontFemale,
        public ?string $frontShiny,
        public ?string $frontShinyFemale,
    ) {}

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->backDefault === $other->backDefault
            && $this->backFemale === $other->backFemale
            && $this->backShiny === $other->backShiny
            && $this->backShinyFemale === $other->backShinyFemale
            && $this->frontDefault === $other->frontDefault
            && $this->frontFemale === $other->frontFemale
            && $this->frontShiny === $other->frontShiny
            && $this->frontShinyFemale === $other->frontShinyFemale;
    }
}
