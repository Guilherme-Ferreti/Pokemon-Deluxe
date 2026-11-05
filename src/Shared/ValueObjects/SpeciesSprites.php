<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Shared\Interfaces\ValueObject;

final readonly class SpeciesSprites implements ValueObject
{
    public function __construct(
        public ?string $back_default,
        public ?string $back_female,
        public ?string $back_shiny,
        public ?string $back_shiny_female,
        public ?string $front_default,
        public ?string $front_female,
        public ?string $front_shiny,
        public ?string $front_shiny_female,
    ) {}

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->back_default === $other->back_default
            && $this->back_female === $other->back_female
            && $this->back_shiny === $other->back_shiny
            && $this->back_shiny_female === $other->back_shiny_female
            && $this->front_default === $other->front_default
            && $this->front_female === $other->front_female
            && $this->front_shiny === $other->front_shiny
            && $this->front_shiny_female === $other->front_shiny_female;
    }
}
