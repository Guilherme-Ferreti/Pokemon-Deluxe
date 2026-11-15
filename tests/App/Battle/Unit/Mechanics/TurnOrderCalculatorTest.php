<?php

declare(strict_types=1);

use App\Battle\Mechanics\TurnOrderCalculator;

it('gives each pokemon a chance to move first that grows with its level', function (int $level, int $opponentLevel, float $expectedChance) {
    $chance = new TurnOrderCalculator()->firstMoverChance($level, $opponentLevel);

    expect($chance)->toEqualWithDelta($expectedChance, 0.01);
})->with([
    'level 100 vs 100' => [100, 100, 50.0],
    'level 1 vs 100'   => [1, 100, 0.99],
    'level 100 vs 1'   => [100, 1, 99.01],
    'level 100 vs 50'  => [100, 50, 66.67],
]);

it('rejects a level below 1', function (int $level, int $opponentLevel) {
    expect(fn () => new TurnOrderCalculator()->firstMoverChance($level, $opponentLevel))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'own level 0'      => [0, 100],
    'opponent level 0' => [100, 0],
]);
