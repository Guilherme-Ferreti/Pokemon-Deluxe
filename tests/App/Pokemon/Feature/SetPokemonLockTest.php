<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.lock', Pokemon::factory()->create()), ['is_locked' => true])
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    actingAs(User::factory()->create())
        ->post(route('pokemon.web.lock', Pokemon::factory()->create()), ['is_locked' => true])
        ->assertNotFound();
});

it('locks the pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create(['is_locked' => false]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.lock', $pokemon), ['is_locked' => true])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Locked');

    expect($pokemon->fresh()->is_locked)->toBeTrue();
});

it('unlocks the pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->locked()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.lock', $pokemon), ['is_locked' => false])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Unlocked');

    expect($pokemon->fresh()->is_locked)->toBeFalse();
});

it('validates the lock', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.lock', $pokemon), ['is_locked' => 'maybe'])
        ->assertSessionHasErrors(['is_locked']);
});
