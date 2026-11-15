<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class BattleAlreadyInProgressException extends DomainException
{
    public function __construct()
    {
        parent::__construct('You are already in a battle.');
    }

    public function getKey(): string
    {
        return 'battle';
    }
}
