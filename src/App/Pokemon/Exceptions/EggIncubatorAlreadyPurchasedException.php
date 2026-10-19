<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\ValueObjects\EggIncubatorIndex;

final class EggIncubatorAlreadyPurchasedException extends DomainException
{
    public function __construct(EggIncubatorIndex $eggIncubatorIndex)
    {
        parent::__construct("The egg incubator #{$eggIncubatorIndex->value} has already been purchased.");
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
