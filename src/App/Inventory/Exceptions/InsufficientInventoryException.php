<?php

declare(strict_types=1);

namespace App\Inventory\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokeball;

final class InsufficientInventoryException extends DomainException
{
    public function __construct(EvolutionItem|Pokeball $item)
    {
        parent::__construct("Not enough {$item->name} in inventory to perform this action.");
    }

    public function getKey(): string
    {
        return 'inventory';
    }
}
