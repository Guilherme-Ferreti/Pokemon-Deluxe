<?php

declare(strict_types=1);

use App\Pokemon\Actions\EggIncubator\HatchEggAction;
use App\Pokemon\Exceptions\EggIncubationNotReadyToHatchException;
use App\Pokemon\Exceptions\EggIncubatorNotActiveException;
use Shared\Models\EggIncubation;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

it('marks a ready-to-hatch egg as hatched', function () {
    travelTo('2026-01-01 12:00:00');

    $user       = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);
    $incubation = EggIncubation::factory()->for($user)->readyToHatch()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);

    $result = new HatchEggAction()->handle($user, new EggIncubatorIndex(1));

    expect($result->is($incubation))->toBeTrue();

    assertDatabaseHas(EggIncubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => '2026-01-01 12:00:00',
    ]);
});

it('does not hatch an egg when the incubator has no active incubation', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    expect(fn () => new HatchEggAction()->handle($user, new EggIncubatorIndex(1)))
        ->toThrow(EggIncubatorNotActiveException::class);

    assertDatabaseCount(EggIncubation::class, 0);
});

it('does not hatch an egg that is still incubating', function () {
    $user       = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);
    $incubation = EggIncubation::factory()->for($user)->incubating()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);

    expect(fn () => new HatchEggAction()->handle($user, new EggIncubatorIndex(1)))
        ->toThrow(EggIncubationNotReadyToHatchException::class);

    assertDatabaseHas(EggIncubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => null,
    ]);
});
