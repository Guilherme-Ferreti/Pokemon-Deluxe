<?php

declare(strict_types=1);

use Shared\Models\EggIncubation;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.egg-incubators.place-egg', 1))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('places an egg in an idle incubator', function () {
    $user    = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);
    $species = Species::factory()->create(['egg_obtainable' => true]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.egg-incubators.place-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(EggIncubation::class, 1);

    assertDatabaseHas(EggIncubation::class, [
        'user_id'             => $user->id,
        'egg_incubator_index' => 1,
        'species_id'          => $species->id,
    ]);
});

it('does not place an egg when the incubator is already incubating', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    EggIncubation::factory()->for($user)->incubating()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.egg-incubators.place-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(EggIncubation::class, 1);
});

it("ignores another user's incubation when placing an egg", function () {
    $user      = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);
    $otherUser = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    EggIncubation::factory()->for($otherUser)->incubating()->create(['egg_incubator_index' => new EggIncubatorIndex(1)]);
    Species::factory()->create(['egg_obtainable' => true]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.egg-incubators.place-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(EggIncubation::class, 2);

    assertDatabaseHas(EggIncubation::class, [
        'user_id'             => $user->id,
        'egg_incubator_index' => 1,
    ]);
});
