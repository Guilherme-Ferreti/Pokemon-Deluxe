<?php

declare(strict_types=1);

namespace App\Pokemon\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Interfaces\ValueObject;
use Shared\Models\Incubation;
use Shared\Models\Species;

final readonly class NewPokemon implements ValueObject
{
    public function __construct(
        public Species $species,
        public int $level,
        public bool $isShiny,
        public PokemonGender $gender,
    ) {
        throw_if($level < 1 || $level > config('game.pokemon.max_level'), InvalidArgumentException::class, sprintf('The level must be between 1 and %d.', config('game.pokemon.max_level')));

        throw_if($species->isGenderless() && $gender !== PokemonGender::GENDERLESS, InvalidArgumentException::class, 'The Pokémon must be genderless.');

        throw_if(! $species->isGenderless() && $gender === PokemonGender::GENDERLESS, InvalidArgumentException::class, 'The Pokémon must be male or female.');

        throw_if($species->gender_rate === PokemonGenderRate::ONLY_MALE && $gender !== PokemonGender::MALE, InvalidArgumentException::class, 'The Pokémon must be male.');

        throw_if($species->gender_rate === PokemonGenderRate::ONLY_FEMALE && $gender !== PokemonGender::FEMALE, InvalidArgumentException::class, 'The Pokémon must be female.');
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->species->is($other->species)
            && $this->level === $other->level
            && $this->isShiny === $other->isShiny
            && $this->gender === $other->gender;
    }

    public static function fromIncubation(Incubation $incubation): self
    {
        return new self(
            species: $incubation->species,
            level: 1,
            isShiny: $incubation->is_shiny,
            gender: $incubation->gender
        );
    }
}
