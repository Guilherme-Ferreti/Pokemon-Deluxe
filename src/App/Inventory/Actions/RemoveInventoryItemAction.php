<?php

declare(strict_types=1);

namespace App\Inventory\Actions;

use App\Inventory\Exceptions\InsufficientInventoryException;
use InvalidArgumentException;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\User;

final class RemoveInventoryItemAction
{
    public function handle(User $user, EvolutionItem|Pokeball $item, int $quantity = 1): void
    {
        throw_if($quantity < 1, InvalidArgumentException::class, 'Quantity must be at least 1.');

        $affected = InventoryItem::query()
            ->whereBelongsTo($user)
            ->forItem($item)
            ->where('quantity', '>=', $quantity)
            ->decrement('quantity', $quantity);

        if ($affected === 0) {
            throw new InsufficientInventoryException($item);
        }
    }
}
