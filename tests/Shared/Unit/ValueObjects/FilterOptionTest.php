<?php

declare(strict_types=1);

use Shared\ValueObjects\FilterOption;

describe('equality', function () {
    it('equals another filter option with same label and value', function () {
        expect((new FilterOption('All', 0))->equals(new FilterOption('All', 0)))->toBeTrue();
    });

    it('does not equal filter option with different label', function () {
        expect((new FilterOption('All', 0))->equals(new FilterOption('None', 0)))->toBeFalse();
    });

    it('does not equal filter option with different value', function () {
        expect((new FilterOption('All', 0))->equals(new FilterOption('All', 1)))->toBeFalse();
    });
});
