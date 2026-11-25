<?php

declare(strict_types=1);

namespace App\Shop\Exceptions;

use Shared\Base\DomainException;

final class ItemNotSellableException extends DomainException
{
    public function __construct(string $itemName)
    {
        parent::__construct("{$itemName} can't be sold to the Shop, only traded.");
    }

    public function getKey(): string
    {
        return 'item';
    }
}
