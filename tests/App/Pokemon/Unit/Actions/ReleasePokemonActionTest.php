<?php

declare(strict_types=1);

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Actions\ReleasePokemonAction;
use App\Pokemon\Exceptions\PartyCannotBeEmptyException;
use App\Pokemon\Exceptions\PokemonLockedException;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

it('releases a pokemon with its known moves', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->withActiveMoves(2)->create();

    new ReleasePokemonAction()->handle($pokemon);

    assertModelMissing($pokemon);
    assertDatabaseMissing('pokemon_move', ['pokemon_id' => $pokemon->id]);
});

it('releases a party pokemon while another stays in the party', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->create();

    new ReleasePokemonAction()->handle($pokemon);

    assertModelMissing($pokemon);
});

it('closes the gap in the party order when a party pokemon is released', function () {
    $user     = User::factory()->create();
    $released = Pokemon::factory()->for($user)->inParty()->create();
    $second   = Pokemon::factory()->for($user)->inParty()->create();
    $third    = Pokemon::factory()->for($user)->inParty()->create();

    new ReleasePokemonAction()->handle($released);

    expect($user->pokemon()->inPartyOrder()->pluck('party_position', 'id')->all())->toBe([$second->id => 1, $third->id => 2]);
});

it('refuses a locked pokemon', function () {
    $pokemon = Pokemon::factory()->inBox()->locked()->create();

    expect(fn () => new ReleasePokemonAction()->handle($pokemon))->toThrow(PokemonLockedException::class);

    assertModelExists($pokemon);
});

it('refuses the last party pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inBox()->create();

    expect(fn () => new ReleasePokemonAction()->handle($pokemon))->toThrow(PartyCannotBeEmptyException::class);

    assertModelExists($pokemon);
});

it('refuses during a battle', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Battle::factory()->for($user)->create();

    expect(fn () => new ReleasePokemonAction()->handle($pokemon))->toThrow(BattleAlreadyInProgressException::class);

    assertModelExists($pokemon);
});
