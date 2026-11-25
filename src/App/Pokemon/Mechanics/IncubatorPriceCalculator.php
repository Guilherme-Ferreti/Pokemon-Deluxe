<?php

declare(strict_types=1);

namespace App\Pokemon\Mechanics;

final class IncubatorPriceCalculator
{
    public function price(int $purchasedCount): int
    {
        return $purchasedCount * (int) config('game.incubation.price_step');
    }
}
