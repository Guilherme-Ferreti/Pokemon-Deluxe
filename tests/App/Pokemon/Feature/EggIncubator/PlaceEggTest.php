<?php

declare(strict_types=1);

use Shared\Models\Incubation;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.incubators.place-egg', 1))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('places an egg in an idle incubator', function () {
    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create(['egg_obtainable' => true]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.place-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'user_id'         => $user->id,
        'incubator_index' => 1,
        'species_id'      => $species->id,
    ]);
});

it('does not place an egg when the incubator is already incubating', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    Incubation::factory()->for($user)->incubating()->create(['incubator_index' => new IncubatorIndex(1)]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.place-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Incubation::class, 1);
});

it("ignores another user's incubation when placing an egg", function () {
    $user      = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $otherUser = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    Incubation::factory()->for($otherUser)->incubating()->create(['incubator_index' => new IncubatorIndex(1)]);
    Species::factory()->create(['egg_obtainable' => true]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.place-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(Incubation::class, 2);

    assertDatabaseHas(Incubation::class, [
        'user_id'         => $user->id,
        'incubator_index' => 1,
    ]);
});
