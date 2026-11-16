<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class BattleAlreadyEndedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('This battle has already ended.');
    }

    public function getKey(): string
    {
        return 'battle';
    }
}
