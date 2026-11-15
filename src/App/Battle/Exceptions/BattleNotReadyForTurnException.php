<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class BattleNotReadyForTurnException extends DomainException
{
    public function __construct()
    {
        parent::__construct("You can't take a turn in this battle right now.");
    }

    public function getKey(): string
    {
        return 'battle';
    }
}
