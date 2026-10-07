<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class EggIncubationAlreadyCompletedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The egg incubation has already completed.');
    }

    public function getKey(): string
    {
        return 'eggIncubation';
    }
}
