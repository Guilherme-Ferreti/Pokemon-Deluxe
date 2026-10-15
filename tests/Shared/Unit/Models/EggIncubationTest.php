<?php

declare(strict_types=1);

use Shared\Enums\EggIncubationStatus;
use Shared\Models\EggIncubation;

describe('status', function () {
    it('reports an egg that is not ready to hatch as incubating', function () {
        $eggIncubation = EggIncubation::factory()->incubating()->create();

        expect($eggIncubation->getStatus())->toBe(EggIncubationStatus::INCUBATING);
    });

    it('reports an egg that is ready to hatch as ready to hatch', function () {
        $eggIncubation = EggIncubation::factory()->readyToHatch()->create();

        expect($eggIncubation->getStatus())->toBe(EggIncubationStatus::READY_TO_HATCH);
    });

    it('reports a hatched egg that has not been captured as ready to capture', function () {
        $eggIncubation = EggIncubation::factory()->readyToCapture()->create();

        expect($eggIncubation->getStatus())->toBe(EggIncubationStatus::READY_TO_CAPTURE);
    });

    it('reports a captured egg as completed', function () {
        $eggIncubation = EggIncubation::factory()->completed()->create();

        expect($eggIncubation->getStatus())->toBe(EggIncubationStatus::COMPLETED);
    });
});
