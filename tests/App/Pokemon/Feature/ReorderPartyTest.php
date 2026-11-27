<?php

declare(strict_types=1);

use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.party.reorder'), ['pokemonIds' => [fake()->uuid()]])
        ->assertRedirectToRoute('auth.web.login.create');
});

it('saves the new party order', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->from(route('pokemon.web.party'))
        ->post(route('pokemon.web.party.reorder'), ['pokemonIds' => [$second->id, $first->id]])
        ->assertRedirect(route('pokemon.web.party'))
        ->assertSessionHasNoErrors();

    expect($user->pokemon()->inPartyOrder()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('refuses a list that is not exactly the party', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->from(route('pokemon.web.party'))
        ->post(route('pokemon.web.party.reorder'), ['pokemonIds' => [$second->id]])
        ->assertRedirect(route('pokemon.web.party'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($user->pokemon()->inPartyOrder()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

it('refuses during a battle', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();

    Battle::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.party'))
        ->post(route('pokemon.web.party.reorder'), ['pokemonIds' => [$second->id, $first->id]])
        ->assertRedirect(route('pokemon.web.party'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($user->pokemon()->inPartyOrder()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

it('validates the new order', function (Closure $makeData) {
    actingAs(User::factory()->create())
        ->from(route('pokemon.web.party'))
        ->post(route('pokemon.web.party.reorder'), $makeData())
        ->assertSessionHasErrors();
})->with([
    'missing'      => [fn () => []],
    'not an array' => [fn () => ['pokemonIds' => 'pokemon']],
    'too many'     => [fn () => ['pokemonIds' => array_map(fn () => fake()->uuid(), range(1, 7))]],
    'not uuids'    => [fn () => ['pokemonIds' => ['pikachu']]],
    'repeated'     => [fn () => ['pokemonIds' => ['9b2c1f60-8a4e-4e43-9a51-3f1c6a1d2e7b', '9b2c1f60-8a4e-4e43-9a51-3f1c6a1d2e7b']]],
]);
