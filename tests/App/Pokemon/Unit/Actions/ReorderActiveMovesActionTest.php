<?php

declare(strict_types=1);

use App\Pokemon\Actions\ReorderActiveMovesAction;
use Shared\Models\Move;
use Shared\Models\Pokemon;

use function Pest\Laravel\assertDatabaseHas;

it('saves the active moves in their new order', function () {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();
    $growl   = Move::factory()->create();
    $scratch = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id  => ['slot' => 1],
        $ember->id   => ['slot' => 2],
        $growl->id   => ['slot' => 3],
        $scratch->id => ['slot' => null],
    ]);

    new ReorderActiveMovesAction()->handle($pokemon, [$growl->id, $tackle->id, $ember->id]);

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $growl->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 2]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 3]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $scratch->id, 'slot' => null]);
});

it('refuses a list that is not exactly the active moves', function (Closure $makeMoveIds) {
    $pokemon = Pokemon::factory()->create();
    $tackle  = Move::factory()->create();
    $ember   = Move::factory()->create();
    $scratch = Move::factory()->create();

    $pokemon->knownMoves()->attach([
        $tackle->id  => ['slot' => 1],
        $ember->id   => ['slot' => 2],
        $scratch->id => ['slot' => null],
    ]);

    expect(fn () => new ReorderActiveMovesAction()->handle($pokemon, $makeMoveIds($tackle, $ember, $scratch)))
        ->toThrow(InvalidArgumentException::class);

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 2]);
})->with([
    'a missing move'  => [fn (Move $tackle, Move $ember, Move $scratch) => [$ember->id]],
    'a pool move'     => [fn (Move $tackle, Move $ember, Move $scratch) => [$ember->id, $scratch->id]],
    'an extra move'   => [fn (Move $tackle, Move $ember, Move $scratch) => [$ember->id, $tackle->id, $scratch->id]],
    'a repeated move' => [fn (Move $tackle, Move $ember, Move $scratch) => [$ember->id, $ember->id]],
]);
