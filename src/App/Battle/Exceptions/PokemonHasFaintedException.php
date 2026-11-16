<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class PokemonHasFaintedException extends DomainException
{
    public function __construct(string $displayName)
    {
        parent::__construct("{$displayName} has fainted and can't battle.");
    }

    public function getKey(): string
    {
        return 'pokemon';
    }
}
