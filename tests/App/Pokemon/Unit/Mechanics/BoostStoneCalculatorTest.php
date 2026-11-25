<?php

declare(strict_types=1);

use App\Pokemon\Mechanics\BoostStoneCalculator;

it('gives the chance a boost stone fails', function (int $currentBoost, float $expectedChance) {
    expect(new BoostStoneCalculator()->failureChance($currentBoost))->toBe($expectedChance);
})->with([
    '+0 → +1'    => [0, 0.0],
    '+19 → +20'  => [19, 0.0],
    '+20 → +21'  => [20, 1.0],
    '+60 → +61'  => [60, 41.0],
    '+99 → +100' => [99, 80.0],
]);

it('refuses a boost with no next level', function (int $currentBoost) {
    expect(fn () => new BoostStoneCalculator()->failureChance($currentBoost))->toThrow(InvalidArgumentException::class);
})->with([
    'negative' => [-1],
    'max'      => [100],
]);
