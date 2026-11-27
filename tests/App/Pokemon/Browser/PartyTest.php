<?php

declare(strict_types=1);

use Shared\Models\Battle;
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

it('shows party pokemon in sending-out order with the first one leading', function () {
    $user         = User::factory()->create();
    $partyPokemon = Pokemon::factory(6)->for($user)->inParty()->create(['boost' => 12]);

    actingAs($user);

    $page = visit(route('pokemon.web.party'))
        ->assertVisible('@party-1-slot__leads-indicator');

    $partyPokemon->values()->each(fn (Pokemon $pokemon, int $index) => $page
        ->assertSeeIn('@party-' . ($index + 1) . '-slot', $pokemon->getDisplayName())
        ->assertSeeIn('@party-' . ($index + 1) . '-slot', "Lv {$pokemon->level} · +{$pokemon->boost}")
    );
});

it('navigates to the pokemon page from a party slot', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.party'))
        ->click('@party-1-slot__link')
        ->assertPathIs(route('pokemon.web.box.show', $pokemon, absolute: false))
        ->assertSeeIn('@pokemon-hero__name', $pokemon->getDisplayName())
        ->assertNoSmoke();
});

it('links empty slots to the box', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(4)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.party'))
        ->assertSeeIn('@party-list__count-indicator', '4 / 6')
        ->click('@party-list__empty-slot-1-link')
        ->assertPathIs(route('pokemon.web.box.index', absolute: false));
});

it('moves a pokemon earlier and keeps the new order after a reload', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.party'))
        ->click('@party-2-slot__move-earlier-button')
        ->wait(1)
        ->refresh()
        ->assertSeeIn('@party-1-slot', $second->getDisplayName())
        ->assertSeeIn('@party-2-slot', $first->getDisplayName());

    expect($user->pokemon()->inPartyOrder()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('disables reordering and links to the battle while one is ongoing', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(2)->inParty()->create();
    Battle::factory()->for($user)->create();

    actingAs($user);

    visit(route('pokemon.web.party'))
        ->assertVisible('@party-page__battle-notice')
        ->assertButtonDisabled('@party-2-slot__move-earlier-button')
        ->assertButtonDisabled('@party-1-slot__move-later-button')
        ->assertAttribute('@party-page__battle-link', 'href', route('battle.web.show', absolute: false));
});
