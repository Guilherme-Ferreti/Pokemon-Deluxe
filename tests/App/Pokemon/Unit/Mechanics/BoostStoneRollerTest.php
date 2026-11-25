<?php

declare(strict_types=1);

use App\Pokemon\Mechanics\BoostStoneRoller;
use Shared\Helpers\RandomHelper;

it('rolls whether a boost stone works', function (bool $isSuccess) {
    RandomHelper::fake([$isSuccess]);

    expect(new BoostStoneRoller()->roll(60))->toBe($isSuccess);
})->with([
    'success' => [true],
    'failure' => [false],
]);

it('always works below the safe boost', function () {
    foreach (range(1, 50) as $attempt) {
        expect(new BoostStoneRoller()->roll(19))->toBeTrue();
    }
});
