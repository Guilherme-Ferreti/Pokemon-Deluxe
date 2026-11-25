<?php

declare(strict_types=1);

namespace App\Shop\Actions;

use App\Economy\Actions\SpendMoneyAction;
use App\Economy\Exceptions\NotEnoughMoneyException;
use App\Inventory\Actions\AddInventoryItemAction;
use App\Inventory\Exceptions\InventoryStackFullException;
use App\Shop\Exceptions\ItemNotForSaleException;
use App\Shop\Mechanics\TmPriceCalculator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\User;

final class BuyItemAction
{
    private User $user;

    private Pokeball|EvolutionItem|Move $item;

    private int $quantity;

    private ?int $unitPrice = null;

    public function handle(User $user, Pokeball|EvolutionItem|Move $item, int $quantity): void
    {
        $this->user     = $user;
        $this->item     = $item;
        $this->quantity = $quantity;

        $this->ensureQuantityIsPositive();

        $this->loadUnitPrice();

        $this->ensureItemIsSold();
        $this->ensureTmIsForSaleToday();
        $this->ensureStackHasRoom();
        $this->ensureUserHasEnoughMoney();

        DB::transaction(function (): void {
            new SpendMoneyAction()->handle($this->user, $this->totalPrice());
            new AddInventoryItemAction()->handle($this->user, $this->item, $this->quantity);
        });
    }

    private function ensureQuantityIsPositive(): void
    {
        throw_if($this->quantity < 1, InvalidArgumentException::class, "The quantity must be at least 1, [{$this->quantity}] given.");
    }

    private function loadUnitPrice(): void
    {
        $this->unitPrice = $this->item instanceof Move
            ? new TmPriceCalculator()->price($this->item)
            : $this->item->price;
    }

    private function ensureItemIsSold(): void
    {
        throw_if($this->unitPrice === null, fn () => ItemNotForSaleException::earnedOnly($this->itemName()));
    }

    private function ensureTmIsForSaleToday(): void
    {
        if (! $this->item instanceof Move) {
            return;
        }

        $todaysTmIds = new GetTodaysTmRotationAction()->handle()->move_ids;

        throw_unless(in_array($this->item->id, $todaysTmIds, true), fn () => ItemNotForSaleException::notInTodaysTms($this->itemName()));
    }

    private function ensureStackHasRoom(): void
    {
        $maxStack = (int) config('game.inventory.max_stack');

        $ownedQuantity = (int) InventoryItem::query()
            ->whereBelongsTo($this->user)
            ->forItem($this->item)
            ->value('quantity');

        throw_if($ownedQuantity + $this->quantity > $maxStack, fn () => new InventoryStackFullException($this->item, $maxStack));
    }

    private function ensureUserHasEnoughMoney(): void
    {
        $money = (int) User::query()->whereKey($this->user->id)->value('money');

        throw_if($money < $this->totalPrice(), fn () => new NotEnoughMoneyException($this->totalPrice(), $money));
    }

    private function totalPrice(): int
    {
        if ($this->unitPrice === null) {
            throw new LogicException('The unit price is checked before the total price is read.');
        }

        return $this->unitPrice * $this->quantity;
    }

    private function itemName(): string
    {
        return $this->item instanceof Move ? $this->item->tmName() : $this->item->name;
    }
}
