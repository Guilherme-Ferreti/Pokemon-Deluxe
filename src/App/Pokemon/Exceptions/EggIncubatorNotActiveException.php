<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use DomainException;

final class EggIncubatorNotActiveException extends DomainException
{
    public function __construct(int $eggIncubatorIndex)
    {
        parent::__construct("The egg incubator #{$eggIncubatorIndex} has not been purchased or is not going through any incubation process.");
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
