<?php

declare(strict_types=1);

namespace App\Inventory\Actions;

use App\Inventory\Exceptions\InventoryStackFullException;
use InvalidArgumentException;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\User;

final class AddInventoryItemAction
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
        $this->ensureQuantityFitsInAStack();

        $this->createEmptyStackIfMissing();

        $isAdded = $this->incrementQuantityIfRoom();

        throw_unless($isAdded, fn () => new InventoryStackFullException($this->item, $this->maxStack()));
    }

    private function ensureQuantityIsPositive(): void
    {
        throw_if($this->quantity < 1, InvalidArgumentException::class, 'Quantity must be at least 1.');
    }

    private function ensureQuantityFitsInAStack(): void
    {
        throw_if($this->quantity > $this->maxStack(), fn () => new InventoryStackFullException($this->item, $this->maxStack()));
    }

    private function createEmptyStackIfMissing(): void
    {
        $now = now();

        InventoryItem::query()->insertOrIgnore([
            'id'         => new InventoryItem()->newUniqueId(),
            'user_id'    => $this->user->id,
            'item_type'  => InventoryItemType::fromModel($this->item)->value,
            'item_id'    => $this->item->getKey(),
            'quantity'   => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function incrementQuantityIfRoom(): bool
    {
        $affectedRows = InventoryItem::query()
            ->whereBelongsTo($this->user)
            ->forItem($this->item)
            ->where('quantity', '<=', $this->maxStack() - $this->quantity)
            ->increment('quantity', $this->quantity);

        return $affectedRows > 0;
    }

    private function maxStack(): int
    {
        return (int) config('game.inventory.max_stack');
    }
}
