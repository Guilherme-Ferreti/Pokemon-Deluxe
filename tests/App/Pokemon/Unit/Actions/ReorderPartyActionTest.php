<?php

declare(strict_types=1);

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Actions\ReorderPartyAction;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

it('saves the party in the new order', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();
    $third  = Pokemon::factory()->for($user)->inParty()->create();

    new ReorderPartyAction()->handle($user, [$third->id, $first->id, $second->id]);

    expect($user->pokemon()->inPartyOrder()->pluck('party_position', 'id')->all())->toBe([$third->id => 1, $first->id => 2, $second->id => 3]);
});

it('refuses a list that is not exactly the party', function (Closure $pokemonIdsFor) {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();

    expect(fn () => new ReorderPartyAction()->handle($user, $pokemonIdsFor($user, $first, $second)))->toThrow(InvalidArgumentException::class)
        ->and($user->pokemon()->inPartyOrder()->pluck('id')->all())->toBe([$first->id, $second->id]);
})->with([
    'a party pokemon missing'  => [fn (User $user, Pokemon $first, Pokemon $second) => [$second->id]],
    'a party pokemon twice'    => [fn (User $user, Pokemon $first, Pokemon $second) => [$second->id, $second->id]],
    'a box pokemon added'      => [fn (User $user, Pokemon $first, Pokemon $second) => [$second->id, $first->id, Pokemon::factory()->for($user)->inBox()->create()->id]],
    "another player's pokemon" => [fn (User $user, Pokemon $first, Pokemon $second) => [$second->id, Pokemon::factory()->inParty()->create()->id]],
]);

it('refuses during a battle', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inParty()->create();
    $second = Pokemon::factory()->for($user)->inParty()->create();

    Battle::factory()->for($user)->create();

    expect(fn () => new ReorderPartyAction()->handle($user, [$second->id, $first->id]))->toThrow(BattleAlreadyInProgressException::class)
        ->and($user->pokemon()->inPartyOrder()->pluck('id')->all())->toBe([$first->id, $second->id]);
});
