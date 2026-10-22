<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class IncubationNotReadyToHatchException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The incubation does not have an egg that is ready to be hatched.');
    }

    public function getKey(): string
    {
        return 'Incubation';
    }
}
