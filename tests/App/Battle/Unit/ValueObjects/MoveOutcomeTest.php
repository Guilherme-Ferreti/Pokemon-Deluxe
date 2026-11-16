<?php

declare(strict_types=1);

use App\Battle\ValueObjects\MoveHit;
use App\Battle\ValueObjects\MoveOutcome;
use Shared\Enums\Ailment;

it('rejects a missed move that still does something', function (bool $hasHits, bool $causesFlinch, ?Ailment $inflictedAilment) {
    expect(fn () => new MoveOutcome(hasHit: false, hits: $hasHits ? [new MoveHit(isCriticalHit: false, randomFactor: 1.0)] : [], causesFlinch: $causesFlinch, inflictedAilment: $inflictedAilment))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'with hits'    => [true, false, null],
    'with flinch'  => [false, true, null],
    'with ailment' => [false, false, Ailment::BURN],
]);

it('rejects the unknown ailment', function () {
    expect(fn () => new MoveOutcome(hasHit: true, hits: [], causesFlinch: false, inflictedAilment: Ailment::UNKNOWN))
        ->toThrow(InvalidArgumentException::class);
});

it('equals an outcome with the same hits in the same order', function () {
    $outcome = new MoveOutcome(
        hasHit: true,
        hits: [new MoveHit(isCriticalHit: true, randomFactor: 0.9), new MoveHit(isCriticalHit: false, randomFactor: 1.0)],
        causesFlinch: false,
        inflictedAilment: null,
    );

    $sameOutcome = new MoveOutcome(
        hasHit: true,
        hits: [new MoveHit(isCriticalHit: true, randomFactor: 0.9), new MoveHit(isCriticalHit: false, randomFactor: 1.0)],
        causesFlinch: false,
        inflictedAilment: null,
    );

    $swappedHitsOutcome = new MoveOutcome(
        hasHit: true,
        hits: [new MoveHit(isCriticalHit: false, randomFactor: 1.0), new MoveHit(isCriticalHit: true, randomFactor: 0.9)],
        causesFlinch: false,
        inflictedAilment: null,
    );

    expect($outcome->equals($sameOutcome))->toBeTrue()
        ->and($outcome->equals($swappedHitsOutcome))->toBeFalse()
        ->and($outcome->equals(MoveOutcome::miss()))->toBeFalse();
});
