<?php

declare(strict_types=1);

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Inventory\Exceptions\InsufficientInventoryException;
use Illuminate\Support\Facades\DB;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('decrements the quantity and leaves other rows alone', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    $pokeball      = Pokeball::factory()->create();
    $otherPokeball = Pokeball::factory()->create();
    $evolutionItem = EvolutionItem::factory()->create();

    $item                   = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 10]);
    $otherItem              = InventoryItem::factory()->for($user)->pokeball($otherPokeball)->create(['quantity' => 4]);
    $otherUsersItem         = InventoryItem::factory()->for($otherUser)->pokeball($pokeball)->create(['quantity' => 3]);
    $evolutionInventoryItem = InventoryItem::factory()->for($user)->evolutionItem($evolutionItem)->create(['quantity' => 2]);

    new RemoveInventoryItemAction()->handle($user, $pokeball, 4);

    assertDatabaseCount(InventoryItem::class, 4);

    expect($item->fresh()->quantity)->toBe(6)
        ->and($otherItem->fresh()->quantity)->toBe(4)
        ->and($otherUsersItem->fresh()->quantity)->toBe(3)
        ->and($evolutionInventoryItem->fresh()->quantity)->toBe(2);
});

it('does not delete the record if the quantity is reduced to zero', function () {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $item     = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 5]);

    new RemoveInventoryItemAction()->handle($user, $pokeball, 5);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($item->fresh()->quantity)->toBe(0);
});

it('throws when removing more than owned and leaves the quantity unchanged', function () {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $item     = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 3]);

    expect(fn () => new RemoveInventoryItemAction()->handle($user, $pokeball, 4))
        ->toThrow(InsufficientInventoryException::class);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($item->fresh()->quantity)->toBe(3);
});

it('throws when removing an item the user never owned', function () {
    $user           = User::factory()->create();
    $otherUser      = User::factory()->create();
    $pokeball       = Pokeball::factory()->create();
    $otherUsersItem = InventoryItem::factory()->for($otherUser)->pokeball($pokeball)->create(['quantity' => 5]);

    expect(fn () => new RemoveInventoryItemAction()->handle($user, $pokeball))
        ->toThrow(InsufficientInventoryException::class);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($otherUsersItem->fresh()->quantity)->toBe(5);
});

it('throws for a quantity of zero or below', function (int $quantity) {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $item     = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 10]);

    expect(fn () => new RemoveInventoryItemAction()->handle($user, $pokeball, $quantity))
        ->toThrow(InvalidArgumentException::class);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($item->fresh()->quantity)->toBe(10);
})->with([
    'zero'     => [0],
    'negative' => [-1],
]);

it('leaves the quantity unchanged when the transaction is rolled back', function () {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $item     = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 10]);

    expect(fn () => DB::transaction(function () use ($user, $pokeball): void {
        new RemoveInventoryItemAction()->handle($user, $pokeball, 4);

        throw new RuntimeException('Rolling back the transaction.');
    }))->toThrow(RuntimeException::class);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($item->fresh()->quantity)->toBe(10);
});

it('removes a tm and a boost stone', function () {
    $user       = User::factory()->create();
    $tm         = Move::factory()->create();
    $boostStone = SpecialItem::factory()->boostStone()->create();

    $tmItem         = InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 2]);
    $boostStoneItem = InventoryItem::factory()->for($user)->specialItem($boostStone)->create(['quantity' => 5]);

    new RemoveInventoryItemAction()->handle($user, $tm);
    new RemoveInventoryItemAction()->handle($user, $boostStone, 3);

    assertDatabaseCount(InventoryItem::class, 2);

    expect($tmItem->fresh()->quantity)->toBe(1)
        ->and($boostStoneItem->fresh()->quantity)->toBe(2);
});

it('names a tm by its code and move when there are not enough', function () {
    $user = User::factory()->create();
    $tm   = Move::factory()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam']);

    new RemoveInventoryItemAction()->handle($user, $tm);
})->throws(InsufficientInventoryException::class, 'Not enough TM 395 Ice Beam in inventory to perform this action.');
