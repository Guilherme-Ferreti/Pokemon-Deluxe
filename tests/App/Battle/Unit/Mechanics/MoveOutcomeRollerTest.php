<?php

declare(strict_types=1);

use App\Battle\Mechanics\MoveOutcomeRoller;
use App\Battle\ValueObjects\MoveHit;
use App\Battle\ValueObjects\MoveOutcome;
use Shared\Enums\Ailment;
use Shared\Enums\MoveCategory;
use Shared\Helpers\RandomHelper;
use Shared\Models\Move;

it('misses without rolling anything else', function () {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE_AILMENT, 'power' => 80, 'accuracy' => 70, 'min_hits' => 2, 'max_hits' => 5, 'flinch_chance' => 30, 'ailment' => Ailment::BURN, 'ailment_chance' => 10]);

    RandomHelper::fake([false]);

    expect(new MoveOutcomeRoller()->roll($move))->toEqual(MoveOutcome::miss());
});

it('hits once with its own critical hit and random factor rolls', function (bool $isCriticalHit, float $randomFactor) {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'power' => 80, 'accuracy' => 100, 'min_hits' => null, 'max_hits' => null, 'flinch_chance' => null, 'ailment' => null, 'ailment_chance' => null]);

    RandomHelper::fake([true, $isCriticalHit, $randomFactor]);

    expect(new MoveOutcomeRoller()->roll($move))->toEqual(new MoveOutcome(
        hasHit: true,
        hits: [new MoveHit(isCriticalHit: $isCriticalHit, randomFactor: $randomFactor)],
        causesFlinch: false,
        inflictedAilment: null,
    ));
})->with([
    'normal hit'   => [false, 1.0],
    'critical hit' => [true, 0.85],
]);

it('rolls the hit count of a multi-hit move and each hit separately', function () {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'power' => 15, 'accuracy' => 85, 'min_hits' => 2, 'max_hits' => 5, 'flinch_chance' => null, 'ailment' => null, 'ailment_chance' => null]);

    RandomHelper::fake([true, 3, true, 0.85, false, 1.0, false, 0.95]);

    expect(new MoveOutcomeRoller()->roll($move))->toEqual(new MoveOutcome(
        hasHit: true,
        hits: [
            new MoveHit(isCriticalHit: true, randomFactor: 0.85),
            new MoveHit(isCriticalHit: false, randomFactor: 1.0),
            new MoveHit(isCriticalHit: false, randomFactor: 0.95),
        ],
        causesFlinch: false,
        inflictedAilment: null,
    ));
});

it('does not roll the hit count when the move always hits the same number of times', function () {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'power' => 35, 'accuracy' => 90, 'min_hits' => 2, 'max_hits' => 2, 'flinch_chance' => null, 'ailment' => null, 'ailment_chance' => null]);

    RandomHelper::fake([true, false, 1.0, false, 0.9]);

    expect(new MoveOutcomeRoller()->roll($move)->hitCount())->toBe(2);
});

it('rolls flinch with the move flinch chance', function (bool $causesFlinch) {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'power' => 60, 'accuracy' => 100, 'min_hits' => null, 'max_hits' => null, 'flinch_chance' => 30, 'ailment' => null, 'ailment_chance' => null]);

    RandomHelper::fake([true, false, 1.0, $causesFlinch]);

    expect(new MoveOutcomeRoller()->roll($move)->causesFlinch)->toBe($causesFlinch);
})->with([
    'flinches'        => [true],
    'does not flinch' => [false],
]);

it('rolls the ailment with the move ailment chance', function (bool $isAilmentRolled, ?Ailment $expectedAilment) {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE_AILMENT, 'power' => 90, 'accuracy' => 100, 'min_hits' => null, 'max_hits' => null, 'flinch_chance' => null, 'ailment' => Ailment::BURN, 'ailment_chance' => 10]);

    RandomHelper::fake([true, false, 1.0, $isAilmentRolled]);

    expect(new MoveOutcomeRoller()->roll($move)->inflictedAilment)->toBe($expectedAilment);
})->with([
    'inflicts the ailment'         => [true, Ailment::BURN],
    'does not inflict the ailment' => [false, null],
]);

it('rolls no hits for a move without power', function () {
    $move = Move::factory()->make(['category' => MoveCategory::AILMENT, 'power' => null, 'accuracy' => 90, 'min_hits' => null, 'max_hits' => null, 'flinch_chance' => null, 'ailment' => Ailment::PARALYSIS, 'ailment_chance' => 100]);

    RandomHelper::fake([true, true]);

    expect(new MoveOutcomeRoller()->roll($move))->toEqual(new MoveOutcome(
        hasHit: true,
        hits: [],
        causesFlinch: false,
        inflictedAilment: Ailment::PARALYSIS,
    ));
});

it('picks burn, paralysis or freeze for a move with an unknown ailment', function () {
    $move = Move::factory()->make(['category' => MoveCategory::DAMAGE_AILMENT, 'power' => 80, 'accuracy' => 100, 'min_hits' => null, 'max_hits' => null, 'flinch_chance' => null, 'ailment' => Ailment::UNKNOWN, 'ailment_chance' => 20]);

    RandomHelper::fake([true, false, 1.0, true, Ailment::FREEZE]);

    expect(new MoveOutcomeRoller()->roll($move)->inflictedAilment)->toBe(Ailment::FREEZE);
});
