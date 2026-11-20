<?php

declare(strict_types=1);

namespace App\Inventory\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokeball;

final class InventoryStackFullException extends DomainException
{
    public function __construct(EvolutionItem|Pokeball $item, int $maxStack)
    {
        parent::__construct(sprintf('You cannot hold more than %s %s.', number_format($maxStack), $item->name));
    }

    public function getKey(): string
    {
        return 'inventory';
    }
}
