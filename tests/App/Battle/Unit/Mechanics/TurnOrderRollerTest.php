<?php

declare(strict_types=1);

use App\Battle\Mechanics\TurnOrderRoller;
use Shared\Helpers\RandomHelper;

it('lets the player move first when the first mover roll succeeds', function (bool $roll) {
    RandomHelper::fake([$roll]);

    expect(new TurnOrderRoller()->rollIsPlayerFirst(playerLevel: 50, opponentLevel: 50))->toBe($roll);
})->with([
    'roll succeeds' => [true],
    'roll fails'    => [false],
]);

it('rejects a level below 1', function () {
    expect(fn () => new TurnOrderRoller()->rollIsPlayerFirst(playerLevel: 0, opponentLevel: 50))
        ->toThrow(InvalidArgumentException::class);
});
