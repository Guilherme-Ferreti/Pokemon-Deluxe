<?php

declare(strict_types=1);

use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.incubators.purchase', 1))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('purchases an incubator the user has not purchased', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.purchase', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it('does not purchase an incubator that has already been purchased', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.purchase', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it("does not modify another user's purchased incubators", function () {
    $user      = User::factory()->create(['purchased_incubator_indexes' => []]);
    $otherUser = User::factory()->create(['purchased_incubator_indexes' => [
        new IncubatorIndex(1),
        new IncubatorIndex(2),
    ]]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.purchase', 1))
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success');

    assertDatabaseCount(User::class, 2);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]))
        ->and($otherUser->fresh()->purchased_incubator_indexes)
        ->toHaveCount(2)
        ->sequence(
            fn ($value) => $value->toMatchObject(['value' => 1]),
            fn ($value) => $value->toMatchObject(['value' => 2]),
        );
});

it('returns 404 for an incubator index outside the configured range', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    actingAs($user)
        ->post(route('pokemon.web.incubators.purchase', config('game.max_incubators_per_user') + 1))
        ->assertNotFound();

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)->toBeEmpty();
});
