<?php

declare(strict_types=1);

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Actions\RemoveFromPartyAction;
use App\Pokemon\Exceptions\PartyCannotBeEmptyException;
use App\Pokemon\Exceptions\PokemonNotInPartyException;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

it('sends a party pokemon to the box', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->create();

    new RemoveFromPartyAction()->handle($pokemon);

    expect($pokemon->fresh()->in_party)->toBeFalse();
});

it('closes the gap in the party order', function () {
    $user    = User::factory()->create();
    $first   = Pokemon::factory()->for($user)->inParty()->create();
    $removed = Pokemon::factory()->for($user)->inParty()->create();
    $third   = Pokemon::factory()->for($user)->inParty()->create();

    new RemoveFromPartyAction()->handle($removed);

    expect($user->pokemon()->inPartyOrder()->pluck('party_position', 'id')->all())->toBe([$first->id => 1, $third->id => 2])
        ->and($removed->fresh()->party_position)->toBeNull();
});

it('refuses to remove the last party pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inBox()->create();

    expect(fn () => new RemoveFromPartyAction()->handle($pokemon))->toThrow(PartyCannotBeEmptyException::class)
        ->and($pokemon->fresh()->in_party)->toBeTrue();
});

it('refuses a pokemon that is not in the party', function () {
    $pokemon = Pokemon::factory()->inBox()->create();

    expect(fn () => new RemoveFromPartyAction()->handle($pokemon))->toThrow(PokemonNotInPartyException::class);
});

it('refuses during a battle', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->create();
    Battle::factory()->for($user)->create();

    expect(fn () => new RemoveFromPartyAction()->handle($pokemon))->toThrow(BattleAlreadyInProgressException::class)
        ->and($pokemon->fresh()->in_party)->toBeTrue();
});
