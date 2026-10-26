<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\ValueObjects\IncubatorIndex;

final class IncubatorEggNotReadyToHatchException extends DomainException
{
    public function __construct(IncubatorIndex $incubatorIndex)
    {
        parent::__construct("The incubator #{$incubatorIndex->value} does not have an egg that is ready to hatch.");
    }

    public function getKey(): string
    {
        return 'incubator';
    }
}
