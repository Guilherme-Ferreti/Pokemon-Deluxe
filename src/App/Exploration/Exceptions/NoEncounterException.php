<?php

declare(strict_types=1);

namespace App\Exploration\Exceptions;

use Shared\Base\DomainException;

final class NoEncounterException extends DomainException
{
    public function __construct()
    {
        parent::__construct('There is no wild Pokémon to battle. Explore an area first.');
    }

    public function getKey(): string
    {
        return 'encounter';
    }
}
