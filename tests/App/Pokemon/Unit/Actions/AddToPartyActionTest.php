<?php

declare(strict_types=1);

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Actions\AddToPartyAction;
use App\Pokemon\Exceptions\PartyFullException;
use App\Pokemon\Exceptions\PokemonAlreadyInPartyException;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

it('adds a box pokemon to the party', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    new AddToPartyAction()->handle($pokemon);

    expect($pokemon->fresh()->in_party)->toBeTrue();
});

it('refuses when the party is full', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Pokemon::factory()->for($user)->inParty()->count(6)->create();

    expect(fn () => new AddToPartyAction()->handle($pokemon))->toThrow(PartyFullException::class)
        ->and($pokemon->fresh()->in_party)->toBeFalse();
});

it('refuses a pokemon already in the party', function () {
    $pokemon = Pokemon::factory()->inParty()->create();

    expect(fn () => new AddToPartyAction()->handle($pokemon))->toThrow(PokemonAlreadyInPartyException::class);
});

it('refuses during a battle', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Battle::factory()->for($user)->create();

    expect(fn () => new AddToPartyAction()->handle($pokemon))->toThrow(BattleAlreadyInProgressException::class)
        ->and($pokemon->fresh()->in_party)->toBeFalse();
});
