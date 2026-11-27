<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class PartyOrderMismatchException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Your party has changed since this page loaded. Here is its current order.');
    }

    public function getKey(): string
    {
        return 'party';
    }
}
