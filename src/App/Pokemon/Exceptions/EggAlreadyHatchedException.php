<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class EggAlreadyHatchedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The egg has already hatched.');
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
