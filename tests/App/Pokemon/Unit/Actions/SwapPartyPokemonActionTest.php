<?php

declare(strict_types=1);

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Actions\SwapPartyPokemonAction;
use App\Pokemon\Exceptions\PokemonAlreadyInPartyException;
use App\Pokemon\Exceptions\PokemonNotInPartyException;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

it('swaps a box pokemon with a party pokemon and keeps the party size', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();
    $leaving = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->count(5)->create();

    new SwapPartyPokemonAction()->handle($joining, $leaving);

    expect($joining->fresh()->in_party)->toBeTrue()
        ->and($leaving->fresh()->in_party)->toBeFalse()
        ->and($user->pokemon()->inParty()->count())->toBe(6);
});

it('refuses a leaving pokemon that is not in the party', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();
    $leaving = Pokemon::factory()->for($user)->inBox()->create();

    expect(fn () => new SwapPartyPokemonAction()->handle($joining, $leaving))->toThrow(PokemonNotInPartyException::class);

    expect($joining->fresh()->in_party)->toBeFalse();
});

it('refuses a joining pokemon that is already in the party', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inParty()->create();
    $leaving = Pokemon::factory()->for($user)->inParty()->create();

    expect(fn () => new SwapPartyPokemonAction()->handle($joining, $leaving))->toThrow(PokemonAlreadyInPartyException::class);

    expect($leaving->fresh()->in_party)->toBeTrue();
});

it('refuses pokemon of different owners', function () {
    $joining = Pokemon::factory()->inBox()->create();
    $leaving = Pokemon::factory()->inParty()->create();

    expect(fn () => new SwapPartyPokemonAction()->handle($joining, $leaving))->toThrow(InvalidArgumentException::class);
});

it('refuses during a battle', function () {
    $user    = User::factory()->create();
    $joining = Pokemon::factory()->for($user)->inBox()->create();
    $leaving = Pokemon::factory()->for($user)->inParty()->create();

    Battle::factory()->for($user)->create();

    expect(fn () => new SwapPartyPokemonAction()->handle($joining, $leaving))->toThrow(BattleAlreadyInProgressException::class);

    expect($joining->fresh()->in_party)->toBeFalse()
        ->and($leaving->fresh()->in_party)->toBeTrue();
});
