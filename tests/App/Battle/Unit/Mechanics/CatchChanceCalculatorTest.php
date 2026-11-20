<?php

declare(strict_types=1);

use App\Battle\Mechanics\CatchChanceCalculator;
use Shared\Enums\Ailment;
use Shared\Models\Species;

it('calculates every catch chance of the spec table', function (bool $isLegendary, int $currentHp, ?Ailment $ailment, float $ballMultiplier, float $expectedChance, int $expectedShownChance) {
    $species = Species::factory()->make(['is_legendary' => $isLegendary, 'is_mythical' => false]);

    $chance = new CatchChanceCalculator()->calculate($species, $currentHp, 100, $ailment, $ballMultiplier);

    expect($chance)->toEqualWithDelta($expectedChance, 0.0001)
        ->and((int) round($chance))->toBe($expectedShownChance);
})->with([
    'poke ball, full hp'                    => [false, 100, null, 1, 10, 10],
    'poke ball, 10% hp'                     => [false, 10, null, 1, 23.5, 24],
    'poke ball, 10% hp, asleep'             => [false, 10, Ailment::SLEEP, 1, 47, 47],
    'ultra ball, 10% hp, asleep'            => [false, 10, Ailment::SLEEP, 2, 94, 94],
    'master ball'                           => [false, 100, null, 255, 100, 100],
    'legendary, poke ball, full hp'         => [true, 100, null, 1, 1, 1],
    'legendary, poke ball, 10% hp'          => [true, 10, null, 1, 2.35, 2],
    'legendary, poke ball, 10% hp, asleep'  => [true, 10, Ailment::SLEEP, 1, 4.7, 5],
    'legendary, ultra ball, 10% hp, asleep' => [true, 10, Ailment::SLEEP, 2, 9.4, 9],
    'legendary, master ball'                => [true, 100, null, 255, 100, 100],
]);

it('uses the legendary base chance for mythicals', function () {
    $species = Species::factory()->make(['is_legendary' => false, 'is_mythical' => true]);

    expect(new CatchChanceCalculator()->calculate($species, 100, 100, null, 1))->toEqualWithDelta(1, 0.0001);
});

it('multiplies the chance by the status effect', function (Ailment $ailment, float $expectedChance) {
    $species = Species::factory()->make(['is_legendary' => false, 'is_mythical' => false]);

    expect(new CatchChanceCalculator()->calculate($species, 100, 100, $ailment, 1))->toEqualWithDelta($expectedChance, 0.0001);
})->with([
    'sleep'     => [Ailment::SLEEP, 20],
    'freeze'    => [Ailment::FREEZE, 20],
    'paralysis' => [Ailment::PARALYSIS, 15],
    'burn'      => [Ailment::BURN, 15],
    'poison'    => [Ailment::POISON, 15],
    'confusion' => [Ailment::CONFUSION, 10],
]);

it('rises to 2.5 times the chance at 0 hp', function () {
    $species = Species::factory()->make(['is_legendary' => false, 'is_mythical' => false]);

    expect(new CatchChanceCalculator()->calculate($species, 0, 100, null, 1))->toEqualWithDelta(25, 0.0001);
});

it('refuses hp outside the max hp', function (int $currentHp, int $maxHp) {
    $species = Species::factory()->make();

    new CatchChanceCalculator()->calculate($species, $currentHp, $maxHp, null, 1);
})->throws(InvalidArgumentException::class)->with([
    'negative hp'  => [-1, 100],
    'above max hp' => [101, 100],
    'zero max hp'  => [0, 0],
]);
