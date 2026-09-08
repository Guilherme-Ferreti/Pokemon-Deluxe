<?php

declare(strict_types=1);

use Shared\Models\Species;

it('returns 100 when capture rate is 255', function () {
    $species = Species::factory()->create(['capture_rate' => 255]);

    expect($species->getCaptureRatePercentage())->toBe(100.0);
});

it('returns 0 when capture rate is 0', function () {
    $species = Species::factory()->create(['capture_rate' => 0]);

    expect($species->getCaptureRatePercentage())->toBe(0.0);
});

it('calculates the capture rate percentage correctly', function () {
    $species = Species::factory()->create(['capture_rate' => 127]);

    expect($species->getCaptureRatePercentage())->toBe(49.8);
});

it('returns the female and male percentage for a gender rate', function (int $genderRate, float $femalePercentage, float $malePercentage) {
    $species = Species::factory()->create(['gender_rate' => $genderRate]);

    expect($species->getFemalePercentage())->toBe($femalePercentage)
        ->and($species->getMalePercentage())->toBe($malePercentage)
        ->and($species->isGenderless())->toBeFalse();
})->with([
    'male only'   => [0, 0.0, 100.0],
    'charmander'  => [1, 12.5, 87.5],
    'pikachu'     => [4, 50.0, 50.0],
    'female only' => [8, 100.0, 0.0],
]);

it('returns zero for both gender percentages when the species is genderless', function () {
    $species = Species::factory()->create(['gender_rate' => -1]);

    expect($species->getFemalePercentage())->toBe(0.0)
        ->and($species->getMalePercentage())->toBe(0.0)
        ->and($species->isGenderless())->toBeTrue();
});
