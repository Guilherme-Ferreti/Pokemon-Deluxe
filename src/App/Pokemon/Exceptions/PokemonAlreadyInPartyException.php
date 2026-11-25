<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class PokemonAlreadyInPartyException extends DomainException
{
    public function __construct(string $pokemonName)
    {
        parent::__construct("{$pokemonName} is already in your party.");
    }

    public function getKey(): string
    {
        return 'party';
    }
}
