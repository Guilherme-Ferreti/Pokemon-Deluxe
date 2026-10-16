<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\ValueObjects\EggIncubatorIndex;

final class EggIncubatorNotIdleException extends DomainException
{
    public function __construct(EggIncubatorIndex $eggIncubatorIndex)
    {
        parent::__construct("The egg incubator #{$eggIncubatorIndex->value} has not been purchased or has an incubation process in progress.");
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
