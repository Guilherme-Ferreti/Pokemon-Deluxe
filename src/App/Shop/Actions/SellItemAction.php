<?php

declare(strict_types=1);

namespace App\Shop\Actions;

use App\Economy\Actions\AddMoneyAction;
use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Shop\Exceptions\ItemNotSellableException;
use App\Shop\Mechanics\ItemPriceCalculator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\User;

final class SellItemAction
{
    private User $user;

    private Pokeball|EvolutionItem|Move $item;

    private int $quantity;

    private ?int $earnedMoney = null;

    public function handle(User $user, Pokeball|EvolutionItem|Move $item, int $quantity): int
    {
        $this->user     = $user;
        $this->item     = $item;
        $this->quantity = $quantity;

        $this->ensureQuantityIsPositive();

        $this->loadEarnedMoney();

        $this->ensureItemIsSellable();

        DB::transaction(function (): void {
            new RemoveInventoryItemAction()->handle($this->user, $this->item, $this->quantity);

            if ($this->earnedMoney() > 0) {
                new AddMoneyAction()->handle($this->user, $this->earnedMoney());
            }
        });

        return $this->earnedMoney();
    }

    private function ensureQuantityIsPositive(): void
    {
        throw_if($this->quantity < 1, InvalidArgumentException::class, "The quantity must be at least 1, [{$this->quantity}] given.");
    }

    private function loadEarnedMoney(): void
    {
        $this->earnedMoney = new ItemPriceCalculator()->sellBackPrice($this->item, $this->quantity);
    }

    private function ensureItemIsSellable(): void
    {
        throw_if($this->earnedMoney === null, fn () => new ItemNotSellableException($this->itemName()));
    }

    private function earnedMoney(): int
    {
        if ($this->earnedMoney === null) {
            throw new LogicException('The item is checked to be sellable before the earned money is read.');
        }

        return $this->earnedMoney;
    }

    private function itemName(): string
    {
        return $this->item instanceof Move ? $this->item->tmName() : $this->item->name;
    }
}
