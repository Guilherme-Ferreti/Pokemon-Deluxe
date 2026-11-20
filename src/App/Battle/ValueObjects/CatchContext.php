<?php

declare(strict_types=1);

namespace App\Battle\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\Ailment;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Interfaces\ValueObject;
use Shared\Models\Species;

final readonly class CatchContext implements ValueObject
{
    public function __construct(
        public int $turn,
        public ?Area $area,
        public bool $isNight,
        public Species $wildSpecies,
        public int $wildLevel,
        public PokemonGender $wildGender,
        public ?Ailment $wildAilment,
        public Species $activeSpecies,
        public int $activeLevel,
        public PokemonGender $activeGender,
        public bool $playerOwnsSpecies,
    ) {
        throw_if($turn < 1, InvalidArgumentException::class, "The turn must be at least 1, [{$turn}] given.");

        throw_if($wildLevel < 1 || $activeLevel < 1, InvalidArgumentException::class, "Levels must be at least 1, [{$wildLevel}] and [{$activeLevel}] given.");
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->turn === $other->turn
            && $this->area === $other->area
            && $this->isNight === $other->isNight
            && $this->wildSpecies->is($other->wildSpecies)
            && $this->wildLevel === $other->wildLevel
            && $this->wildGender === $other->wildGender
            && $this->wildAilment === $other->wildAilment
            && $this->activeSpecies->is($other->activeSpecies)
            && $this->activeLevel === $other->activeLevel
            && $this->activeGender === $other->activeGender
            && $this->playerOwnsSpecies === $other->playerOwnsSpecies;
    }
}
