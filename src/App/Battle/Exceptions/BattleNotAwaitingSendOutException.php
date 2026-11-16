<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class BattleNotAwaitingSendOutException extends DomainException
{
    public function __construct()
    {
        parent::__construct("There's no Pokémon to send out right now.");
    }

    public function getKey(): string
    {
        return 'battle';
    }
}
