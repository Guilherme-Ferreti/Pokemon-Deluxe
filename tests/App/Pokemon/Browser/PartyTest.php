<?php

declare(strict_types=1);

use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the party page without errors', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(3)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.party'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Your Party');
});

it('shows party pokemon', function () {
    $user         = User::factory()->create();
    $partyPokemon = Pokemon::factory(6)->for($user)->inParty()->create();

    actingAs($user);

    $page = visit(route('pokemon.web.party'));

    $partyPokemon->each(fn (Pokemon $pokemon) => $page
        ->assertSee($pokemon->getDisplayName())
        ->assertSee('Lv. ' . $pokemon->level)
    );
});

it('navigates to the pokemon page from a party slot', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.party'))
        ->click("@party-list__pokemon-{$pokemon->id}-link")
        ->assertPathIs(route('pokemon.web.show', $pokemon, absolute: false))
        ->assertSeeIn('@pokemon-hero__name', $pokemon->getDisplayName())
        ->assertNoSmoke();
});
