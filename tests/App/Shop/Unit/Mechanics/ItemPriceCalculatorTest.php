<?php

declare(strict_types=1);

use App\Shop\Mechanics\ItemPriceCalculator;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

it('prices balls and evolution items by their price and tms by their power', function () {
    $calculator = new ItemPriceCalculator;

    expect($calculator->price(Pokeball::factory()->make(['price' => 600])))->toBe(600)
        ->and($calculator->price(EvolutionItem::factory()->make(['price' => null])))->toBeNull()
        ->and($calculator->price(Move::factory()->plainDamage()->make(['power' => 60])))->toBe(5000);
});

it('pays half the price for each item sold back, rounded down', function () {
    $calculator = new ItemPriceCalculator;

    expect($calculator->sellBackPrice(Pokeball::factory()->make(['price' => 1000]), 3))->toBe(1500)
        ->and($calculator->sellBackPrice(Pokeball::factory()->make(['price' => 201]), 1))->toBe(100)
        ->and($calculator->sellBackPrice(Pokeball::factory()->make(['price' => null]), 1))->toBeNull();
});
