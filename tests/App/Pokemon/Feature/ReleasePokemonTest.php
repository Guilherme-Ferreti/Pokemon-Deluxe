<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\delete;

it('redirects guests to login', function () {
    delete(route('pokemon.web.release', Pokemon::factory()->inBox()->create()))
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    actingAs(User::factory()->create())
        ->delete(route('pokemon.web.release', Pokemon::factory()->inBox()->create()))
        ->assertNotFound();

    assertDatabaseCount(Pokemon::class, 1);
});

it('releases the pokemon and goes to the box', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->shiny()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->delete(route('pokemon.web.release', $pokemon))
        ->assertRedirectToRoute('pokemon.web.box.index')
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Released')
        ->assertInertiaFlash('toast.message', "{$pokemon->getDisplayName()} was released.");

    assertModelMissing($pokemon);
});

it('warns when the pokemon is locked', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->locked()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->delete(route('pokemon.web.release', $pokemon))
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.message', "{$pokemon->getDisplayName()} is locked. Unlock it first.");

    assertDatabaseCount(Pokemon::class, 1);
});
