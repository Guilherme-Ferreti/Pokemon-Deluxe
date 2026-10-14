<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class EggIncubatorNotIdleException extends DomainException
{
    public function __construct(int $eggIncubatorIndex)
    {
        parent::__construct("The egg incubator #{$eggIncubatorIndex} has not been purchased or has an incubation process in progress.");
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
