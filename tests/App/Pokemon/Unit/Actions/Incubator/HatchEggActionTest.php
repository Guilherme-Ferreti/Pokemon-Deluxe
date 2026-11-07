<?php

declare(strict_types=1);

use App\Pokemon\Actions\Incubator\HatchEggAction;
use App\Pokemon\Exceptions\IncubatorEggNotReadyToHatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use Shared\Models\Incubation;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

it('marks a ready-to-hatch egg as hatched', function () {
    travelTo('2026-01-01 12:00:00');

    $user       = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $incubation = Incubation::factory()->for($user)->readyToHatch()->create();

    $result = new HatchEggAction()->handle($user, $incubation->incubator_index);

    expect($result->is($incubation))->toBeTrue();

    assertDatabaseHas(Incubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => '2026-01-01 12:00:00',
    ]);
});

it('does not hatch an egg when the incubator has no active incubation', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    expect(fn () => new HatchEggAction()->handle($user, new IncubatorIndex(1)))
        ->toThrow(IncubatorNotActiveException::class);

    assertDatabaseCount(Incubation::class, 0);
});

it('does not hatch an egg that is still incubating', function () {
    $user       = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $incubation = Incubation::factory()->for($user)->incubating()->create();

    expect(fn () => new HatchEggAction()->handle($user, $incubation->incubator_index))
        ->toThrow(IncubatorEggNotReadyToHatchException::class);

    assertDatabaseHas(Incubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => null,
    ]);
});
