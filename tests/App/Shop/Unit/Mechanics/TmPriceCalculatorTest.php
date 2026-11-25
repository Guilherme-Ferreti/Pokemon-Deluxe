<?php

declare(strict_types=1);

use App\Shop\Mechanics\TmPriceCalculator;
use Shared\Models\Move;

it('prices a tm by its move power', function (int $power, int $price) {
    $move = Move::factory()->make(['power' => $power, 'is_z_move' => false, 'is_dynamax_move' => false]);

    expect(new TmPriceCalculator()->price($move))->toBe($price);
})->with([
    'power 59' => [59, 2000],
    'power 60' => [60, 5000],
    'power 89' => [89, 5000],
    'power 90' => [90, 10000],
]);

it('prices a status move tm at 3,000', function () {
    $move = Move::factory()->make(['power' => null, 'is_z_move' => false, 'is_dynamax_move' => false]);

    expect(new TmPriceCalculator()->price($move))->toBe(3000);
});

it('does not price z-move and dynamax move tms', function (string $flag) {
    $move = Move::factory()->make(['power' => 100, 'is_z_move' => false, 'is_dynamax_move' => false, $flag => true]);

    expect(new TmPriceCalculator()->price($move))->toBeNull();
})->with(['is_z_move', 'is_dynamax_move']);
