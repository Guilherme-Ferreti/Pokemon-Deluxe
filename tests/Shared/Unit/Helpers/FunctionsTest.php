<?php

declare(strict_types=1);

use Shared\Enums\Generation;
use Shared\Enums\PokeballType;
use Shared\Enums\SortDirection;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

use function Pest\Laravel\actingAs;

describe('currentUser', function () {
    it('returns the authenticated user', function () {
        $user = User::factory()->create();

        actingAs($user);

        expect(currentUser()->is($user))->toBeTrue();
    });
});

describe('toIntIfWhole', function () {
    it('returns an integer when the value has no fractional part', function (int|float $value, int $expected) {
        expect(toIntIfWhole($value))->toBe($expected);
    })->with([
        'integer'        => [3, 3],
        'whole float'    => [3.0, 3],
        'zero'           => [0.0, 0],
        'negative whole' => [-2.0, -2],
    ]);

    it('returns the float when the value has a fractional part', function (float $value) {
        expect(toIntIfWhole($value))->toBe($value);
    })->with([
        'positive' => 3.5,
        'negative' => -2.25,
    ]);
});

describe('enumLabels', function () {
    it('uses the label method of a string backed enum', function () {
        expect(enumLabels(SortDirection::class))->toBe([
            'asc'  => 'Ascending',
            'desc' => 'Descending',
        ]);
    });

    it('uses the label method of an int backed enum', function () {
        $labels = enumLabels(Generation::class);

        expect($labels)->toHaveCount(9)
            ->and($labels[1])->toBe('Gen I')
            ->and($labels[9])->toBe('Gen IX');
    });

    it('falls back to a headline of the case name when the enum has no label method', function () {
        expect(enumLabels(PokeballType::class))->toBe([
            'standard' => 'Standard',
            'special'  => 'Special',
            'apricorn' => 'Apricorn',
        ]);
    });
});

describe('value_object_array_contains', function () {
    it('returns true when an equal value object is present', function () {
        $indexes = [new EggIncubatorIndex(1), new EggIncubatorIndex(2)];

        expect(value_object_array_contains($indexes, new EggIncubatorIndex(2)))->toBeTrue();
    });

    it('returns false when no equal value object is present', function () {
        $indexes = [new EggIncubatorIndex(1), new EggIncubatorIndex(2)];

        expect(value_object_array_contains($indexes, new EggIncubatorIndex(3)))->toBeFalse();
    });

    it('returns false for an empty array', function () {
        expect(value_object_array_contains([], new EggIncubatorIndex(1)))->toBeFalse();
    });
});

describe('value_object_array_diff', function () {
    it('returns the value objects that are not present in the other array', function () {
        $array1 = [new EggIncubatorIndex(1), new EggIncubatorIndex(2), new EggIncubatorIndex(3)];
        $array2 = [new EggIncubatorIndex(2)];

        $result = value_object_array_diff($array1, $array2);

        expect($result)
            ->tohaveLength(2)
            ->sequence(
                fn ($value) => $value->toMatchObject(['value' => 1]),
                fn ($value) => $value->toMatchObject(['value' => 3]),
            );
    });

    it('returns every value object when the other array is empty', function () {
        $array1 = [new EggIncubatorIndex(1), new EggIncubatorIndex(2)];

        $result = value_object_array_diff($array1, []);

        expect($result)
            ->tohaveLength(2)
            ->sequence(
                fn ($value) => $value->toMatchObject(['value' => 1]),
                fn ($value) => $value->toMatchObject(['value' => 2]),
            );
    });

    it('returns an empty array when every value object is present in the other array', function () {
        $array1 = [new EggIncubatorIndex(1), new EggIncubatorIndex(2)];
        $array2 = [new EggIncubatorIndex(1), new EggIncubatorIndex(2)];

        expect(value_object_array_diff($array1, $array2))->toBeEmpty();
    });
});
