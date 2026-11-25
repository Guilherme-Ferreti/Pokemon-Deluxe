<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class MaxBoostReachedException extends DomainException
{
    public function __construct(string $pokemonName, int $maxBoost)
    {
        parent::__construct("{$pokemonName} is already at +{$maxBoost}.");
    }

    public function getKey(): string
    {
        return 'boost';
    }
}
