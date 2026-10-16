<?php

declare(strict_types=1);

namespace Shared\Interfaces;

interface ValueObject
{
    public function equals(ValueObject $other): bool;
}
