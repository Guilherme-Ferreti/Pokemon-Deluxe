<?php

declare(strict_types=1);

use App\Pokemon\Mechanics\IncubatorPriceCalculator;

it('prices each next incubator 5,000 more than the last', function (int $purchasedCount, int $price) {
    expect(new IncubatorPriceCalculator()->price($purchasedCount))->toBe($price);
})->with([
    'first'  => [0, 0],
    'second' => [1, 5000],
    'third'  => [2, 10000],
    'tenth'  => [9, 45000],
]);
