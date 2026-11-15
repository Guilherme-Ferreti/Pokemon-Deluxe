<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class EmptyPartyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Your party has no Pokémon to battle with.');
    }

    public function getKey(): string
    {
        return 'party';
    }
}
