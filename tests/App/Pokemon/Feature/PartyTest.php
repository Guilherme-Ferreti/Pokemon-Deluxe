<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('web.pokemon.party'))
        ->assertRedirectToRoute('web.auth.login.create');
});

it('loads the party page with expected structure', function () {
    $user         = User::factory()->create();
    $partyPokemon = Pokemon::factory(3)->for($user)->inParty()->create();

    actingAs($user)
        ->get(route('web.pokemon.party'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pokemon/Party')
            ->has('party', 3)
            ->where('party.0.id', $partyPokemon[0]->id)
            ->where('party.0.name', $partyPokemon[0]->getName())
            ->where('party.0.frontSprite', $partyPokemon[0]->getFrontSprite())
            ->where('party.0.level', $partyPokemon[0]->level)
        );
});

it('excludes box pokemon from the party', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user)
        ->get(route('web.pokemon.party'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('party', 0)
        );
});

it("shows only the authenticated user's party pokemon", function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory(10)->inParty()->create();

    actingAs($user)
        ->get(route('web.pokemon.party'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('party', 1)
            ->where('party.0.id', $pokemon->id)
        );
});
