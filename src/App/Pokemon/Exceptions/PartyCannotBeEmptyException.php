<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class PartyCannotBeEmptyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Your party needs at least one Pokémon.');
    }

    public function getKey(): string
    {
        return 'party';
    }
}
