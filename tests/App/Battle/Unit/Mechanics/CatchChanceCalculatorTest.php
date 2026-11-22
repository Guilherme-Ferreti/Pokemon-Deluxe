<?php

declare(strict_types=1);

use App\Battle\Mechanics\CatchChanceCalculator;
use Shared\Enums\Ailment;
use Shared\Models\Species;

it('calculates every catch chance of the spec table', function (int $catchRate, int $currentHp, ?Ailment $ailment, float $ballMultiplier, float $expectedChance, int $expectedShownChance) {
    $species = Species::factory()->make(['catch_rate' => $catchRate]);

    $chance = new CatchChanceCalculator()->calculate($species, $currentHp, 100, $ailment, $ballMultiplier);

    expect($chance)->toEqualWithDelta($expectedChance, 0.0001)
        ->and((int) round($chance))->toBe($expectedShownChance);
})->with([
    'rate 190, poke ball, full hp'        => [190, 100, null, 1, 74.5098, 75],
    'rate 190, poke ball, 10% hp'         => [190, 10, null, 1, 100, 100],
    'rate 45, poke ball, full hp'         => [45, 100, null, 1, 17.6471, 18],
    'rate 45, poke ball, 10% hp'          => [45, 10, null, 1, 41.4706, 41],
    'rate 45, poke ball, 10% hp, asleep'  => [45, 10, Ailment::SLEEP, 1, 82.9412, 83],
    'rate 45, ultra ball, 10% hp, asleep' => [45, 10, Ailment::SLEEP, 2, 100, 100],
    'rate 45, master ball'                => [45, 100, null, 255, 100, 100],
    'rate 3, poke ball, full hp'          => [3, 100, null, 1, 1.1765, 1],
    'rate 3, poke ball, 10% hp'           => [3, 10, null, 1, 2.7647, 3],
    'rate 3, poke ball, 10% hp, asleep'   => [3, 10, Ailment::SLEEP, 1, 5.5294, 6],
    'rate 3, ultra ball, 10% hp, asleep'  => [3, 10, Ailment::SLEEP, 2, 11.0588, 11],
    'rate 3, master ball'                 => [3, 100, null, 255, 100, 100],
]);

it('multiplies the chance by the status effect', function (Ailment $ailment, float $expectedChance) {
    $species = Species::factory()->make(['catch_rate' => 51]);

    expect(new CatchChanceCalculator()->calculate($species, 100, 100, $ailment, 1))->toEqualWithDelta($expectedChance, 0.0001);
})->with([
    'sleep'     => [Ailment::SLEEP, 40],
    'freeze'    => [Ailment::FREEZE, 40],
    'paralysis' => [Ailment::PARALYSIS, 30],
    'burn'      => [Ailment::BURN, 30],
    'poison'    => [Ailment::POISON, 30],
    'confusion' => [Ailment::CONFUSION, 20],
]);

it('rises to 2.5 times the chance at 0 hp', function () {
    $species = Species::factory()->make(['catch_rate' => 51]);

    expect(new CatchChanceCalculator()->calculate($species, 0, 100, null, 1))->toEqualWithDelta(50, 0.0001);
});

it('refuses a species without a catch rate', function () {
    $species = Species::factory()->make(['catch_rate' => null]);

    new CatchChanceCalculator()->calculate($species, 100, 100, null, 1);
})->throws(InvalidArgumentException::class);

it('refuses hp outside the max hp', function (int $currentHp, int $maxHp) {
    $species = Species::factory()->make();

    new CatchChanceCalculator()->calculate($species, $currentHp, $maxHp, null, 1);
})->throws(InvalidArgumentException::class)->with([
    'negative hp'  => [-1, 100],
    'above max hp' => [101, 100],
    'zero max hp'  => [0, 0],
]);
