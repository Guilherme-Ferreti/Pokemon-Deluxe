<?php

declare(strict_types=1);

namespace App\Inventory\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;

final class InsufficientInventoryException extends DomainException
{
    public function __construct(EvolutionItem|Pokeball|Move|SpecialItem $item)
    {
        parent::__construct('Not enough ' . ($item instanceof Move ? $item->tmName() : $item->name) . ' in inventory to perform this action.');
    }

    public function getKey(): string
    {
        return 'inventory';
    }
}
