<?php

declare(strict_types=1);

use App\Battle\ValueObjects\MoveHit;

it('rejects a random factor outside above 0 to 1', function (float $randomFactor) {
    expect(fn () => new MoveHit(isCriticalHit: false, randomFactor: $randomFactor))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'zero'      => [0.0],
    'above one' => [1.01],
]);

it('equals a hit with the same values', function () {
    expect(new MoveHit(isCriticalHit: true, randomFactor: 0.9)->equals(new MoveHit(isCriticalHit: true, randomFactor: 0.9)))->toBeTrue()
        ->and(new MoveHit(isCriticalHit: true, randomFactor: 0.9)->equals(new MoveHit(isCriticalHit: false, randomFactor: 0.9)))->toBeFalse()
        ->and(new MoveHit(isCriticalHit: true, randomFactor: 0.9)->equals(new MoveHit(isCriticalHit: true, randomFactor: 0.95)))->toBeFalse();
});
