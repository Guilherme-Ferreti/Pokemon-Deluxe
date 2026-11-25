<?php

declare(strict_types=1);

use App\Inventory\Exceptions\InsufficientInventoryException;
use App\Pokemon\Actions\TeachTmAction;
use App\Pokemon\Exceptions\MoveAlreadyKnownException;
use App\Pokemon\Exceptions\TmRequirementNotMetException;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('teaches the move into the first empty slot and uses up the tm', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create(['level' => 30]);
    $tm      = Move::factory()->plainDamage()->create();
    $stack   = InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 2]);

    $pokemon->knownMoves()->attach([
        Move::factory()->plainDamage()->create()->id => ['slot' => 1],
        Move::factory()->plainDamage()->create()->id => ['slot' => 3],
    ]);

    new TeachTmAction()->handle($pokemon, $tm);

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tm->id, 'slot' => 2]);

    expect($stack->fresh()->quantity)->toBe(1);
});

it('puts the move in the pool when every active slot is taken', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->withActiveMoves(4)->create(['level' => 30]);
    $tm      = Move::factory()->plainDamage()->create();

    InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]);

    new TeachTmAction()->handle($pokemon, $tm);

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tm->id, 'slot' => null]);
});

it('teaches a z-move tm to a level 100 pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create(['level' => 100]);
    $tm      = Move::factory()->plainDamage()->create(['is_z_move' => true]);

    InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]);

    new TeachTmAction()->handle($pokemon, $tm);

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tm->id]);
});

it('refuses and keeps the tm', function (array $moveAttributes, int $level, bool $isKnown, string $exception) {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create(['level' => $level]);
    $tm      = Move::factory()->plainDamage()->create($moveAttributes);
    $stack   = InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]);

    if ($isKnown) {
        $pokemon->knownMoves()->attach($tm->id, ['slot' => null]);
    }

    expect(fn () => new TeachTmAction()->handle($pokemon, $tm))->toThrow($exception);

    assertDatabaseCount('pokemon_move', $isKnown ? 1 : 0);

    expect($stack->fresh()->quantity)->toBe(1);
})->with([
    'already known'          => [[], 30, true, MoveAlreadyKnownException::class],
    'z-move below 100'       => [['is_z_move' => true], 99, false, TmRequirementNotMetException::class],
    'dynamax move below 100' => [['is_dynamax_move' => true], 99, false, TmRequirementNotMetException::class],
]);

it('refuses a tm the owner does not have', function () {
    $pokemon = Pokemon::factory()->create(['level' => 30]);
    $tm      = Move::factory()->plainDamage()->create();

    expect(fn () => new TeachTmAction()->handle($pokemon, $tm))->toThrow(InsufficientInventoryException::class);

    assertDatabaseCount('pokemon_move', 0);
});
