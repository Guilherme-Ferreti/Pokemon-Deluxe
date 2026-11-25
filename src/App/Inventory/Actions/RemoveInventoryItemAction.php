<?php

declare(strict_types=1);

namespace App\Inventory\Actions;

use App\Inventory\Exceptions\InsufficientInventoryException;
use InvalidArgumentException;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\User;

final class RemoveInventoryItemAction
{
    private User $user;

    private EvolutionItem|Pokeball|Move|SpecialItem $item;

    private int $quantity;

    public function handle(User $user, EvolutionItem|Pokeball|Move|SpecialItem $item, int $quantity = 1): void
    {
        $this->user     = $user;
        $this->item     = $item;
        $this->quantity = $quantity;

        $this->ensureQuantityIsPositive();

        $isRemoved = $this->decrementQuantityIfEnough();

        throw_unless($isRemoved, new InsufficientInventoryException($this->item));
    }

    private function ensureQuantityIsPositive(): void
    {
        throw_if($this->quantity < 1, InvalidArgumentException::class, 'Quantity must be at least 1.');
    }

    private function decrementQuantityIfEnough(): bool
    {
        $affectedRows = InventoryItem::query()
            ->whereBelongsTo($this->user)
            ->forItem($this->item)
            ->where('quantity', '>=', $this->quantity)
            ->decrement('quantity', $this->quantity);

        return $affectedRows > 0;
    }
}
