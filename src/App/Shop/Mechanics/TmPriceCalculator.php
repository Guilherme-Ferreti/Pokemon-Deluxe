<?php

declare(strict_types=1);

namespace App\Shop\Mechanics;

use Shared\Models\Move;

final class TmPriceCalculator
{
    public function price(Move $move): ?int
    {
        if ($move->is_z_move || $move->is_dynamax_move) {
            return null;
        }

        if ($move->power === null) {
            return (int) config('game.shop.tm_prices.status_move');
        }

        /** @var array<int, int> $pricesByMinPower */
        $pricesByMinPower = config('game.shop.tm_prices.by_min_power');

        krsort($pricesByMinPower);

        foreach ($pricesByMinPower as $minPower => $price) {
            if ($move->power >= $minPower) {
                return $price;
            }
        }

        return null;
    }
}
