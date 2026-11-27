<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

it('redirects guests to login', function () {
    delete(route('pokemon.web.party.remove', Pokemon::factory()->inParty()->create()))
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    actingAs(User::factory()->create())
        ->delete(route('pokemon.web.party.remove', Pokemon::factory()->inParty()->create()))
        ->assertNotFound();
});

it('sends the pokemon to the box', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->delete(route('pokemon.web.party.remove', $pokemon))
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Removed from party')
        ->assertInertiaFlash('toast.message', "{$pokemon->getDisplayName()} went to your Box.");

    expect($pokemon->fresh()->in_party)->toBeFalse();
});

it('warns when it is the last party pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->delete(route('pokemon.web.party.remove', $pokemon))
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.message', 'Your party needs at least one Pokémon.');

    expect($pokemon->fresh()->in_party)->toBeTrue();
});
