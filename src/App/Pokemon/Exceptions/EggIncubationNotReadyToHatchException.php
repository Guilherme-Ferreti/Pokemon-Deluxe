<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class EggIncubationNotReadyToHatchException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The egg incubation does not have an egg that is ready to be hatched.');
    }

    public function getKey(): string
    {
        return 'eggIncubation';
    }
}
