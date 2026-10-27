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
use function Pest\Laravel\travelTo;

it('redirects guests to login', function () {
    post(route('pokemon.web.incubators.hatch-egg', 1))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('hatches a ready-to-hatch egg', function () {
    travelTo('2026-01-01 12:00:00');

    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create();

    $incubation = Incubation::factory()->for($user)->for($species)->readyToHatch()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.hatch-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => '2026-01-01 12:00:00',
    ]);
});

it('does not hatch an egg that is still incubating', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    $incubation = Incubation::factory()->for($user)->incubating()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.hatch-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => null,
    ]);
});

it('does not hatch an egg when the incubator is not active', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.hatch-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Incubation::class, 0);
});

it("does not hatch another user's egg", function () {
    $user      = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $otherUser = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    $incubation = Incubation::factory()->for($otherUser)->readyToHatch()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.hatch-egg', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'         => $incubation->id,
        'hatched_at' => null,
    ]);
});
