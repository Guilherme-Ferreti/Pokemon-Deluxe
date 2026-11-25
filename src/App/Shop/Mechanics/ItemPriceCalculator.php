<?php

declare(strict_types=1);

namespace App\Shop\Mechanics;

use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

final class ItemPriceCalculator
{
    public function price(Pokeball|EvolutionItem|Move $item): ?int
    {
        return $item instanceof Move ? new TmPriceCalculator()->price($item) : $item->price;
    }

    public function sellBackPrice(Pokeball|EvolutionItem|Move $item, int $quantity): ?int
    {
        $price = $this->price($item);

        if ($price === null) {
            return null;
        }

        return (int) floor($price * (float) config('game.shop.sell_back_ratio') * $quantity);
    }
}
