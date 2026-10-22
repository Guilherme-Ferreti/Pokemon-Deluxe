<?php

declare(strict_types=1);

use Shared\Enums\IncubationStatus;
use Shared\Models\Incubation;

describe('status', function () {
    it('reports an egg that is not ready to hatch as incubating', function () {
        $incubation = Incubation::factory()->incubating()->create();

        expect($incubation->getStatus())->toBe(IncubationStatus::INCUBATING);
    });

    it('reports an egg that is ready to hatch as ready to hatch', function () {
        $incubation = Incubation::factory()->readyToHatch()->create();

        expect($incubation->getStatus())->toBe(IncubationStatus::READY_TO_HATCH);
    });

    it('reports a hatched egg that has not been caught as ready to catch', function () {
        $incubation = Incubation::factory()->readyToCatch()->create();

        expect($incubation->getStatus())->toBe(IncubationStatus::READY_TO_CATCH);
    });

    it('reports a caught egg as completed', function () {
        $incubation = Incubation::factory()->completed()->create();

        expect($incubation->getStatus())->toBe(IncubationStatus::COMPLETED);
    });
});
