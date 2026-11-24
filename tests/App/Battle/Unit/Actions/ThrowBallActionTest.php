<?php

declare(strict_types=1);

use App\Battle\Actions\ThrowBallAction;
use App\Battle\Events\BattleEnded;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\NotAWildBattleException;
use App\Inventory\Exceptions\InsufficientInventoryException;
use App\Pokemon\Events\PokemonCaught;
use App\Pokemon\Exceptions\BoxFullException;
use Illuminate\Support\Facades\Event;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('catches the wild pokemon, which joins the party, and ends the battle as caught', function () {
    Event::fake([PokemonCaught::class, BattleEnded::class]);

    $user     = User::factory()->create();
    $pikachu  = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey   = Species::factory()->withBaseMoves()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL], 'gender_rate' => PokemonGenderRate::EQUAL]);
    $pokemon  = Pokemon::factory()->for($user)->inParty()->create(['species_id' => $pikachu->id, 'level' => 50, 'experience' => 50_000]);
    $pokeBall = Pokeball::factory()->create(['slug' => 'poke-ball', 'name' => 'Poké Ball', 'catch_rate' => 1]);

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 3]);

    $combatant = [
        'pokemonId'                => $pokemon->id,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => ['move-1'],
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
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'level' => 50, 'isShiny' => true, 'gender' => PokemonGender::FEMALE, 'types' => [PokemonType::NORMAL]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([true]);

    $battle = new ThrowBallAction()->handle($battle, $pokeBall)->fresh();

    assertDatabaseCount(Pokemon::class, 2);
    assertDatabaseHas(Pokemon::class, [
        'user_id'     => $user->id,
        'species_id'  => $pidgey->id,
        'level'       => 50,
        'is_shiny'    => true,
        'gender'      => PokemonGender::FEMALE,
        'in_party'    => true,
        'pokeball_id' => $pokeBall->id,
    ]);
    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_id' => $pokeBall->id, 'quantity' => 2]);

    $caughtPokemon = Pokemon::query()->where('species_id', $pidgey->id)->firstOrFail();

    expect($battle?->status)->toBe(BattleStatus::CAUGHT)
        ->and($battle?->state->caughtPokemonId)->toBe($caughtPokemon->id)
        ->and($battle?->state->log)->toEqual([
            new BattleLogEntry(2, BattleLogKind::EVENT, 'You threw a Poké Ball!'),
            new BattleLogEntry(2, BattleLogKind::RESULT, 'Gotcha! Shiny Pidgey was caught!'),
            new BattleLogEntry(2, BattleLogKind::EVENT, 'Pikachu gained 375 XP!'),
        ])
        ->and($pokemon->fresh()?->experience)->toBe(50_375);

    Event::assertDispatched(PokemonCaught::class, fn (PokemonCaught $event) => $event->pokemonId === $caughtPokemon->id && $event->userId === $user->id);
    Event::assertDispatched(BattleEnded::class, fn (BattleEnded $event) => $event->status === BattleStatus::CAUGHT && $event->isWin);
});

it('sends the caught pokemon to the box when the party is full', function () {
    config()->set('game.user.max_party_size', 1);

    $user       = User::factory()->create();
    $pikachu    = Species::factory()->create(['name' => 'Pikachu']);
    $pidgey     = Species::factory()->create(['name' => 'Pidgey', 'gender_rate' => PokemonGenderRate::EQUAL]);
    $pokemon    = Pokemon::factory()->for($user)->inParty()->create(['species_id' => $pikachu->id, 'level' => 50]);
    $masterBall = Pokeball::factory()->create(['slug' => 'master-ball', 'name' => 'Master Ball']);

    InventoryItem::factory()->for($user)->pokeball($masterBall)->create(['quantity' => 1]);

    $combatant = [
        'pokemonId'                => $pokemon->id,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'state' => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'gender' => PokemonGender::FEMALE]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([]);

    new ThrowBallAction()->handle($battle, $masterBall);

    assertDatabaseCount(Pokemon::class, 2);
    assertDatabaseHas(Pokemon::class, ['species_id' => $pidgey->id, 'in_party' => false]);
    assertDatabaseHas(InventoryItem::class, ['item_id' => $masterBall->id, 'quantity' => 0]);

    expect($battle->fresh()?->status)->toBe(BattleStatus::CAUGHT);
});

it('uses the ball and lets the wild pokemon attack when it breaks free', function () {
    Event::fake([PokemonCaught::class]);

    $user      = User::factory()->create();
    $pikachu   = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey    = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $tackle    = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $pokemon   = Pokemon::factory()->for($user)->inParty()->create(['species_id' => $pikachu->id, 'level' => 50]);
    $ultraBall = Pokeball::factory()->create(['slug' => 'ultra-ball', 'name' => 'Ultra Ball', 'catch_rate' => 1]);

    InventoryItem::factory()->for($user)->pokeball($ultraBall)->create(['quantity' => 3]);

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
        'state' => new BattleState(
            turn: 3,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([false, $tackle->id, true, false, 1.0]);

    $battle = new ThrowBallAction()->handle($battle, $ultraBall)->fresh();

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_id' => $ultraBall->id, 'quantity' => 2]);

    expect($battle?->status)->toBe(BattleStatus::ONGOING)
        ->and($battle?->state->turn)->toBe(4)
        ->and($battle?->state->playerSide->activeCombatant()->currentHp)->toBe(218 - 46)
        ->and($battle?->state->log)->toEqual([
            new BattleLogEntry(3, BattleLogKind::EVENT, 'You threw an Ultra Ball!'),
            new BattleLogEntry(3, BattleLogKind::EVENT, 'Oh no! The Pokémon broke free!'),
            new BattleLogEntry(3, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(3, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
        ]);

    Event::assertNotDispatched(PokemonCaught::class);
});

it('refuses to throw a ball in a battle that is not wild', function () {
    $user     = User::factory()->create();
    $pokeBall = Pokeball::factory()->create();
    $battle   = Battle::factory()->for($user)->create(['type' => BattleType::TRAINER]);

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 3]);

    expect(fn () => new ThrowBallAction()->handle($battle, $pokeBall))->toThrow(NotAWildBattleException::class);

    assertDatabaseHas(InventoryItem::class, ['item_id' => $pokeBall->id, 'quantity' => 3]);
});

it('refuses to throw a ball when the battle has ended or awaits a send-out', function (BattleStatus $status, bool $isAwaitingSendOut) {
    $user     = User::factory()->create();
    $pokeBall = Pokeball::factory()->create();
    $battle   = Battle::factory()->for($user)->create(['status' => $status]);

    $battle->update(['state' => $battle->state->withAwaitingSendOut($isAwaitingSendOut)]);

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 3]);

    expect(fn () => new ThrowBallAction()->handle($battle, $pokeBall))->toThrow(BattleNotReadyForTurnException::class);

    assertDatabaseHas(InventoryItem::class, ['item_id' => $pokeBall->id, 'quantity' => 3]);
})->with([
    'ended'             => [BattleStatus::WON, false],
    'awaiting send-out' => [BattleStatus::ONGOING, true],
]);

it('refuses to throw a ball when the box is full, without using it', function () {
    config()->set('game.user.max_box_size', 1);

    $user     = User::factory()->create();
    $pokeBall = Pokeball::factory()->create();
    $battle   = Battle::factory()->for($user)->create();

    Pokemon::factory()->for($user)->create();
    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 3]);

    expect(fn () => new ThrowBallAction()->handle($battle, $pokeBall))->toThrow(BoxFullException::class);

    assertDatabaseHas(InventoryItem::class, ['item_id' => $pokeBall->id, 'quantity' => 3]);
});

it('refuses to throw a ball the player does not have', function () {
    $user       = User::factory()->create();
    $pikachu    = Species::factory()->create();
    $pidgey     = Species::factory()->create(['gender_rate' => PokemonGenderRate::EQUAL]);
    $masterBall = Pokeball::factory()->create(['slug' => 'master-ball']);

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'state' => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'gender' => PokemonGender::FEMALE]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    expect(fn () => new ThrowBallAction()->handle($battle, $masterBall))->toThrow(InsufficientInventoryException::class);

    assertDatabaseCount(Pokemon::class, 0);

    expect($battle->fresh()?->status)->toBe(BattleStatus::ONGOING);
});
