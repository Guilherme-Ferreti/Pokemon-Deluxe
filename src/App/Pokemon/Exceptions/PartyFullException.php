<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class PartyFullException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Your party is full. Send a Pokémon to the Box first.');
    }

    public function getKey(): string
    {
        return 'party';
    }
}
