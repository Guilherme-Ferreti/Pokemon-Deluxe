<?php

declare(strict_types=1);

use App\Pokemon\Actions\Incubator\ListIncubatorsAction;
use App\Pokemon\Enums\IncubatorStatus;
use Shared\Models\Incubation;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

beforeEach(function () {
    config(['game.user.max_incubators' => 3]);
});

it('lists every incubator keyed by index, idle when purchased and not purchased otherwise', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [
        new IncubatorIndex(1),
        new IncubatorIndex(3),
    ]]);

    $incubators = new ListIncubatorsAction()->handle($user);

    expect($incubators->keys()->all())->toBe([1, 2, 3])
        ->and($incubators->map->index->map->value->all())->toBe([1 => 1, 2 => 2, 3 => 3])
        ->and($incubators->map->status()->all())->toBe([
            1 => IncubatorStatus::IDLE,
            2 => IncubatorStatus::NOT_PURCHASED,
            3 => IncubatorStatus::IDLE,
        ])
        ->and($incubators->map->isIdle()->all())->toBe([1 => true, 2 => false, 3 => true])
        ->and($incubators->map->isActive()->all())->toBe([1 => false, 2 => false, 3 => false]);
});

it('marks a purchased incubator as active with the status of its incubation', function (string $state, IncubatorStatus $status) {
    $user = User::factory()->create(['purchased_incubator_indexes' => [
        new IncubatorIndex(1),
        new IncubatorIndex(2),
    ]]);
    $incubation = Incubation::factory()->for($user)->{$state}()->create(['incubator_index' => new IncubatorIndex(1)]);

    $incubators = new ListIncubatorsAction()->handle($user);

    expect($incubators[1]->isActive())->toBeTrue()
        ->and($incubators[1]->isIdle())->toBeFalse()
        ->and($incubators[1]->status())->toBe($status)
        ->and($incubators[1]->incubation?->id)->toBe($incubation->id)
        ->and($incubators[2]->isActive())->toBeFalse()
        ->and($incubators[2]->status())->toBe(IncubatorStatus::IDLE);
})->with([
    'incubating'     => ['incubating', IncubatorStatus::INCUBATING],
    'ready to hatch' => ['readyToHatch', IncubatorStatus::READY_TO_HATCH],
    'ready to catch' => ['readyToCatch', IncubatorStatus::READY_TO_CATCH],
]);

it('does not mark an incubator as active when its incubation is completed', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    Incubation::factory()->for($user)->completed()->create(['incubator_index' => new IncubatorIndex(1)]);

    $incubators = new ListIncubatorsAction()->handle($user);

    expect($incubators[1]->isActive())->toBeFalse()
        ->and($incubators[1]->status())->toBe(IncubatorStatus::IDLE);
});

it("does not count another user's incubation as active", function () {
    $user      = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $otherUser = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    Incubation::factory()->for($otherUser)->incubating()->create(['incubator_index' => new IncubatorIndex(1)]);

    $incubators = new ListIncubatorsAction()->handle($user);

    expect($incubators[1]->isActive())->toBeFalse()
        ->and($incubators[1]->status())->toBe(IncubatorStatus::IDLE);
});

it('reports every incubator as not purchased when the user has purchased none', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    $incubators = new ListIncubatorsAction()->handle($user);

    expect($incubators->map->status()->all())->toBe([
        1 => IncubatorStatus::NOT_PURCHASED,
        2 => IncubatorStatus::NOT_PURCHASED,
        3 => IncubatorStatus::NOT_PURCHASED,
    ]);
});
