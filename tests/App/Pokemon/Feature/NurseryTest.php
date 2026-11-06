<?php

declare(strict_types=1);

use App\Pokemon\Enums\IncubatorStatus;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Incubation;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('pokemon.web.nursery'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the nursery page with expected structure', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    actingAs($user)
        ->get(route('pokemon.web.nursery'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pokemon/pages/Nursery/index')
            ->has('incubators', config('game.user.max_incubators'))
            ->where('incubators.0.index', 1)
            ->where('incubators.0.status', IncubatorStatus::NOT_PURCHASED->value)
            ->has('pokeballs')
        );
});

it('reports the status of each incubator', function () {
    $user = User::factory()->create([
        'purchased_incubator_indexes' => array_map(fn (int $index) => new IncubatorIndex($index), [1, 2, 3, 4, 5]),
    ]);

    Incubation::factory()->for($user)->incubating()->create(['incubator_index' => new IncubatorIndex(1)]);
    Incubation::factory()->for($user)->readyToHatch()->create(['incubator_index' => new IncubatorIndex(2)]);
    Incubation::factory()->for($user)->readyToCatch()->create(['incubator_index' => new IncubatorIndex(3)]);
    Incubation::factory()->for($user)->completed()->create(['incubator_index' => new IncubatorIndex(4)]);

    actingAs($user)
        ->get(route('pokemon.web.nursery'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('incubators.0.status', IncubatorStatus::INCUBATING->value)
            ->where('incubators.1.status', IncubatorStatus::READY_TO_HATCH->value)
            ->where('incubators.2.status', IncubatorStatus::READY_TO_CATCH->value)
            ->where('incubators.3.status', IncubatorStatus::IDLE->value)
            ->where('incubators.4.status', IncubatorStatus::IDLE->value)
            ->where('incubators.5.status', IncubatorStatus::NOT_PURCHASED->value)
        );
});

it("ignores other users' incubations", function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    Incubation::factory()->incubating()->create(['incubator_index' => new IncubatorIndex(1)]);

    actingAs($user)
        ->get(route('pokemon.web.nursery'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('incubators.0.status', IncubatorStatus::IDLE->value)
        );
});
