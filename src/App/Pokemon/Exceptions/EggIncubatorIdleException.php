<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class EggIncubatorIdleException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The egg incubator is idle.');
    }

    public function getKey(): string
    {
        return 'eggIncubator';
    }
}
