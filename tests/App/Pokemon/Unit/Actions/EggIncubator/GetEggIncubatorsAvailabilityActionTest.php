<?php

declare(strict_types=1);

use App\Pokemon\Actions\EggIncubator\GetEggIncubatorsAvailabilityAction;
use Shared\Models\EggIncubation;
use Shared\Models\User;

beforeEach(function () {
    config(['game.max_egg_incubators_per_user' => 3]);
});

it('lists purchased and unpurchased incubator indexes when none are in use', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1, 3]]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability)->toBe([
        'all'          => [1, 2, 3],
        'purchased'    => [1, 3],
        'notPurchased' => [2],
        'active'       => [],
        'idle'         => [1, 3],
    ]);
});

it('marks a purchased incubator as active while an egg is incubating', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1, 2]]);
    EggIncubation::factory()->for($user)->incubating()->create(['egg_incubator_index' => 1]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability)->toMatchArray(['active' => [1], 'idle' => [2]]);
});

it('does not mark an incubator as active when its incubation is completed', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    EggIncubation::factory()->for($user)->completed()->create(['egg_incubator_index' => 1]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability)->toMatchArray(['active' => [], 'idle' => [1]]);
});

it("does not count another user's incubation as active", function () {
    $user      = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    $otherUser = User::factory()->create(['purchased_egg_incubator_indexes' => [1]]);
    EggIncubation::factory()->for($otherUser)->incubating()->create(['egg_incubator_index' => 1]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability)->toMatchArray(['active' => [], 'idle' => [1]]);
});

it('reports every incubator as unpurchased when the user has purchased none', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => []]);

    $availability = new GetEggIncubatorsAvailabilityAction()->handle($user);

    expect($availability)->toBe([
        'all'          => [1, 2, 3],
        'purchased'    => [],
        'notPurchased' => [1, 2, 3],
        'active'       => [],
        'idle'         => [],
    ]);
});
