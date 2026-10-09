<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class EggStillIncubatingException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The egg is still incubating.');
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
