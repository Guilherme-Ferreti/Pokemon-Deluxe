<?php

declare(strict_types=1);

use App\Pokemon\Actions\EggIncubator\GetEggIncubatorsAvailabilityAction;
use Shared\Models\EggIncubation;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

beforeEach(function () {
    config(['game.max_egg_incubators_per_user' => 3]);
});

it('returns all, purchased, not purchased, active, and idle egg incubators indexes', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [
        new EggIncubatorIndex(1),
        new EggIncubatorIndex(3),
    ]]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability)
        ->toHaveKeys(['all', 'purchased', 'notPurchased', 'active', 'idle'])
        ->each(fn ($value) => $value->toBeArray()->toContainOnlyInstancesOf(EggIncubatorIndex::class))
        ->and($availability['all'])->toHaveCount(3)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]), fn ($value) => $value->toMatchObject(['value' => 2]), fn ($value) => $value->toMatchObject(['value' => 3]))
        ->and($availability['purchased'])->toHaveCount(2)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]), fn ($value) => $value->toMatchObject(['value' => 3]))
        ->and($availability['notPurchased'])->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 2]))
        ->and($availability['active'])->toBeEmpty()
        ->and($availability['idle'])->toHaveCount(2)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]), fn ($value) => $value->toMatchObject(['value' => 3]));
});

it('marks a purchased incubator as active while an egg is incubating', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [
        new EggIncubatorIndex(1),
        new EggIncubatorIndex(2),
    ]]);

    EggIncubation::factory()->for($user)->incubating()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability['active'])
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]))
        ->and($availability['idle'])->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 2]));
});

it('does not mark an incubator as active when its incubation is completed', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    EggIncubation::factory()->for($user)->completed()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability['active'])->toBeEmpty()
        ->and($availability['idle'])->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it("does not count another user's incubation as active", function () {
    $user      = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);
    $otherUser = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    EggIncubation::factory()->for($otherUser)->incubating()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability['active'])->toBeEmpty()
        ->and($availability['idle'])->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it('reports every incubator as unpurchased when the user has purchased none', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => []]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability['all'])
        ->toHaveCount(3)
        ->sequence(
            fn ($value) => $value->toMatchObject(['value' => 1]),
            fn ($value) => $value->toMatchObject(['value' => 2]),
            fn ($value) => $value->toMatchObject(['value' => 3]),
        )
        ->and($availability['notPurchased'])->toHaveCount(3)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]), fn ($value) => $value->toMatchObject(['value' => 2]), fn ($value) => $value->toMatchObject(['value' => 3]))
        ->and($availability['purchased'])->toBeEmpty()
        ->and($availability['active'])->toBeEmpty()
        ->and($availability['idle'])->toBeEmpty();
});
