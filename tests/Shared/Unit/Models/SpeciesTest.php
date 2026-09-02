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
