<?php

declare(strict_types=1);

use Shared\Models\Species;

describe('catch rate', function () {
    it('returns 100 when catch rate is 255', function () {
        $species = Species::factory()->create(['catch_rate' => 255]);

        expect($species->getCatchRatePercentage())->toBe(100);
    });

    it('returns 0 when catch rate is 0', function () {
        $species = Species::factory()->create(['catch_rate' => 0]);

        expect($species->getCatchRatePercentage())->toBe(0);
    });

    it('calculates the catch rate percentage correctly', function () {
        $species = Species::factory()->create(['catch_rate' => 127]);

        expect($species->getCatchRatePercentage())->toBe(49.8);
    });
});
