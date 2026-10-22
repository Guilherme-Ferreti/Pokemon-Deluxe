<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\ValueObjects\IncubatorIndex;

final class IncubatorNotActiveException extends DomainException
{
    public function __construct(IncubatorIndex $incubatorIndex)
    {
        parent::__construct("The incubator #{$incubatorIndex->value} has not been purchased or is not going through any incubation process.");
    }

    public function getKey(): string
    {
        return 'incubator';
    }
}
