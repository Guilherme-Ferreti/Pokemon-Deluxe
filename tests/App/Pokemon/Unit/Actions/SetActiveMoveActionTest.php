<?php

declare(strict_types=1);

use App\Pokemon\Actions\SetActiveMoveAction;
use App\Pokemon\Exceptions\MoveNotKnownException;
use Shared\Models\Move;
use Shared\Models\Pokemon;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('puts a pool move in an active slot and sends the replaced move back to the pool', function () {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();
    $growl   = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id => ['slot' => 1],
        $ember->id  => ['slot' => 2],
        $growl->id  => ['slot' => null],
    ]);

    new SetActiveMoveAction()->handle($pokemon, $growl, 2);

    assertDatabaseCount('pokemon_move', 3);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => null]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $growl->id, 'slot' => 2]);
});

it('swaps two active moves', function () {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id => ['slot' => 1],
        $ember->id  => ['slot' => 3],
    ]);

    new SetActiveMoveAction()->handle($pokemon, $tackle, 3);

    assertDatabaseCount('pokemon_move', 2);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 3]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 1]);
});

it('puts a pool move in an empty slot', function () {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();
    $growl   = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id => ['slot' => 1],
        $ember->id  => ['slot' => 2],
        $growl->id  => ['slot' => null],
    ]);

    new SetActiveMoveAction()->handle($pokemon, $growl, 3);

    assertDatabaseCount('pokemon_move', 3);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 2]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $growl->id, 'slot' => 3]);
});

it('moves an active move to an empty slot and leaves its old slot empty', function () {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id => ['slot' => 1],
        $ember->id  => ['slot' => 2],
    ]);

    new SetActiveMoveAction()->handle($pokemon, $tackle, 4);

    assertDatabaseCount('pokemon_move', 2);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 4]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 2]);
});

it('changes nothing when the move is already in that slot', function () {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id => ['slot' => 1],
        $ember->id  => ['slot' => 2],
    ]);

    new SetActiveMoveAction()->handle($pokemon, $tackle, 1);

    assertDatabaseCount('pokemon_move', 2);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 2]);
});

it('rejects a move the pokemon does not know', function () {
    $pokemon     = Pokemon::factory()->create();
    $tackle      = Move::factory()->create();
    $unknownMove = Move::factory()->create();

    $pokemon->knownMoves()->attach($tackle->id, ['slot' => 1]);

    expect(fn () => new SetActiveMoveAction()->handle($pokemon, $unknownMove, 1))
        ->toThrow(MoveNotKnownException::class);

    assertDatabaseCount('pokemon_move', 1);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 1]);
});

it('rejects a slot outside 1 to 4', function (int $slot) {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id => ['slot' => 1],
        $ember->id  => ['slot' => null],
    ]);

    expect(fn () => new SetActiveMoveAction()->handle($pokemon, $ember, $slot))
        ->toThrow(InvalidArgumentException::class);

    assertDatabaseCount('pokemon_move', 2);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => null]);
})->with([
    'slot 0' => 0,
    'slot 5' => 5,
]);
