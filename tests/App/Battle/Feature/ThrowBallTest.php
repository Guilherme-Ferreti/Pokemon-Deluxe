<?php

declare(strict_types=1);

use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('battle.web.throw-ball'), ['pokeball' => 'poke-ball'])
        ->assertRedirectToRoute('auth.web.login.create');
});

it('catches the wild pokemon with the chosen ball', function () {
    $user = User::factory()->create();

    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey  = Species::factory()->withBaseMoves()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL]]);
    $pokemon = Pokemon::factory()->for($user)->inParty()->create(['species_id' => $pikachu->id, 'level' => 50]);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemonId'                => $pokemon->id,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'types' => [PokemonType::NORMAL]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);
    $pokeBall = Pokeball::factory()->create(['slug' => 'poke-ball', 'name' => 'Poké Ball', 'catch_rate' => 1]);

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 3]);

    RandomHelper::fake([true]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.throw-ball'), ['pokeball' => 'poke-ball'])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasNoErrors();

    $caughtPokemon = Pokemon::query()->where('pokeball_id', $pokeBall->id)->firstOrFail();

    expect($battle->fresh())
        ->status->toBe(BattleStatus::CAUGHT)
        ->state->caughtPokemonId->toBe($caughtPokemon->id);

    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_id' => $pokeBall->id, 'quantity' => 2]);
});

it('uses up the ball and keeps the battle going when the wild pokemon breaks free', function () {
    $user = User::factory()->create();

    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey  = Species::factory()->withBaseMoves()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL]]);
    $pokemon = Pokemon::factory()->for($user)->inParty()->create(['species_id' => $pikachu->id, 'level' => 50]);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemonId'                => $pokemon->id,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'types' => [PokemonType::NORMAL]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);
    $pokeBall = Pokeball::factory()->create(['slug' => 'poke-ball', 'name' => 'Poké Ball', 'catch_rate' => 1]);

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 1]);

    RandomHelper::fake([false, $tackle->id, true, false, 1.0]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.throw-ball'), ['pokeball' => 'poke-ball'])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasNoErrors();

    expect($battle->fresh())
        ->status->toBe(BattleStatus::ONGOING)
        ->state->turn->toBe(3)
        ->state->caughtPokemonId->toBeNull();

    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_id' => $pokeBall->id, 'quantity' => 0]);
});

it('rejects a ball that does not exist', function (mixed $pokeball) {
    $user   = User::factory()->create();
    $battle = Battle::factory()->for($user)->create();

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.throw-ball'), ['pokeball' => $pokeball])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasErrors('pokeball');

    expect($battle->fresh()?->state->turn)->toBe(1);
})->with([
    'missing' => [null],
    'unknown' => ['unknown-ball'],
]);

it('warns when the player has none of the chosen ball', function () {
    $user = User::factory()->create();

    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey  = Species::factory()->withBaseMoves()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL]]);
    $pokemon = Pokemon::factory()->for($user)->inParty()->create(['species_id' => $pikachu->id, 'level' => 50]);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemonId'                => $pokemon->id,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'types' => [PokemonType::NORMAL]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    Pokeball::factory()->create(['slug' => 'poke-ball']);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.throw-ball'), ['pokeball' => 'poke-ball'])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($battle->fresh())
        ->status->toBe(BattleStatus::ONGOING)
        ->state->turn->toBe(2);
});

it('warns when there is no battle in progress', function () {
    $user             = User::factory()->create();
    $otherUsersBattle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    Pokeball::factory()->create(['slug' => 'poke-ball']);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.throw-ball'), ['pokeball' => 'poke-ball'])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($otherUsersBattle->fresh()?->status)->toBe(BattleStatus::ONGOING);
});
