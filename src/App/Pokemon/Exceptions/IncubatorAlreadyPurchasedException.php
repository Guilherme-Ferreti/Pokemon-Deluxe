<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\ValueObjects\IncubatorIndex;

final class IncubatorAlreadyPurchasedException extends DomainException
{
    public function __construct(IncubatorIndex $incubatorIndex)
    {
        parent::__construct("The incubator #{$incubatorIndex->value} has already been purchased.");
    }

    public function getKey(): string
    {
        return 'incubator';
    }
}
