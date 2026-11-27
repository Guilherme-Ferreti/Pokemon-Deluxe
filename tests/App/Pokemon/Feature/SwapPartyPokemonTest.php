<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.party.swap', Pokemon::factory()->inBox()->create()), ['leavingPokemonId' => fake()->uuid()])
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    $user    = User::factory()->create();
    $leaving = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->post(route('pokemon.web.party.swap', Pokemon::factory()->inBox()->create()), ['leavingPokemonId' => $leaving->id])
        ->assertNotFound();
});

it("gives 404 when the leaving pokemon is another player's", function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user)
        ->post(route('pokemon.web.party.swap', $joining), ['leavingPokemonId' => Pokemon::factory()->inParty()->create()->id])
        ->assertNotFound();

    expect($joining->fresh()->in_party)->toBeFalse();
});

it('swaps the pokemon with a party pokemon', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();
    $leaving = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $joining))
        ->post(route('pokemon.web.party.swap', $joining), ['leavingPokemonId' => $leaving->id])
        ->assertRedirect(route('pokemon.web.box.show', $joining))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Added to party')
        ->assertInertiaFlash('toast.message', "{$joining->getDisplayName()} joined your party and {$leaving->getDisplayName()} went to your Box.");

    expect($joining->fresh()->in_party)->toBeTrue()
        ->and($leaving->fresh()->in_party)->toBeFalse();
});

it('warns when the leaving pokemon is not in the party', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();
    $leaving = Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $joining))
        ->post(route('pokemon.web.party.swap', $joining), ['leavingPokemonId' => $leaving->id])
        ->assertRedirect(route('pokemon.web.box.show', $joining))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($joining->fresh()->in_party)->toBeFalse();
});

it('validates the leaving pokemon', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $joining))
        ->post(route('pokemon.web.party.swap', $joining), ['leavingPokemonId' => 'not-a-uuid'])
        ->assertSessionHasErrors(['leavingPokemonId']);
});
