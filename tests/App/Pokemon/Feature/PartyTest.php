<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('pokemon.web.party'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the party page in party order', function () {
    $user   = User::factory()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create(['party_position' => 2]);
    $first  = Pokemon::factory()->for($user)->inParty()->create(['party_position' => 1]);

    actingAs($user)
        ->get(route('pokemon.web.party'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pokemon/pages/Party/index')
            ->has('party', 2)
            ->where('party.0.id', $first->id)
            ->where('party.0.name', $first->getDisplayName())
            ->where('party.0.gender', $first->gender->value)
            ->where('party.0.portrait', $first->getDisplayPortrait())
            ->where('party.0.level', $first->level)
            ->where('party.0.boost', $first->boost)
            ->where('party.0.types', $first->species->types->map(fn ($type) => $type->value)->all())
            ->where('party.1.id', $second->id)
            ->where('isBattleOngoing', false)
            ->where('maxPartySize', config('game.user.max_party_size'))
        );
});

it('says when a battle is ongoing', function () {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create();

    actingAs($user)
        ->get(route('pokemon.web.party'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('isBattleOngoing', true)
        );
});

it('excludes box pokemon from the party', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user)
        ->get(route('pokemon.web.party'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('party', 0)
        );
});

it("shows only the authenticated user's party pokemon", function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory(10)->inParty()->create();

    actingAs($user)
        ->get(route('pokemon.web.party'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('party', 1)
            ->where('party.0.id', $pokemon->id)
        );
});
