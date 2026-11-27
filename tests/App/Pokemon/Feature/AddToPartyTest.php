<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.party.add', Pokemon::factory()->inBox()->create()))
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    actingAs(User::factory()->create())
        ->post(route('pokemon.web.party.add', Pokemon::factory()->inBox()->create()))
        ->assertNotFound();
});

it('adds the pokemon to the party', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->shiny()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.party.add', $pokemon))
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Added to party')
        ->assertInertiaFlash('toast.message', "{$pokemon->getDisplayName()} joined your party.");

    expect($pokemon->fresh()->in_party)->toBeTrue();
});

it('warns when the party is full', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Pokemon::factory()->for($user)->inParty()->count(6)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.party.add', $pokemon))
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.message', 'Your party is full. Send a Pokémon to the Box first.');

    expect($pokemon->fresh()->in_party)->toBeFalse();
});
