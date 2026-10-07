<?php

declare(strict_types=1);

namespace Shared\Base;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    abstract public function getKey(): string;
}
