<?php

declare(strict_types=1);

use App\Inventory\Actions\AddInventoryItemAction;
use App\Inventory\Exceptions\InventoryStackFullException;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('creates a new stack', function (string $itemClass, InventoryItemType $expectedItemType) {
    $user = User::factory()->create();
    $item = $itemClass::factory()->create();

    new AddInventoryItemAction()->handle($user, $item, 20);

    assertDatabaseCount(InventoryItem::class, 1);
    assertDatabaseHas(InventoryItem::class, [
        'user_id'   => $user->id,
        'item_type' => $expectedItemType,
        'item_id'   => $item->id,
        'quantity'  => 20,
    ]);
})->with([
    'pokeball'       => [Pokeball::class, InventoryItemType::POKEBALL],
    'evolution item' => [EvolutionItem::class, InventoryItemType::EVOLUTION_ITEM],
    'tm'             => [Move::class, InventoryItemType::TM],
    'special item'   => [SpecialItem::class, InventoryItemType::SPECIAL_ITEM],
]);

it('adds one item by default', function () {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();

    new AddInventoryItemAction()->handle($user, $pokeball);

    expect(InventoryItem::query()->sole()->quantity)->toBe(1);
});

it('grows an existing stack and leaves other rows alone', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    $pokeball      = Pokeball::factory()->create();
    $otherPokeball = Pokeball::factory()->create();

    $item           = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 10]);
    $otherItem      = InventoryItem::factory()->for($user)->pokeball($otherPokeball)->create(['quantity' => 4]);
    $otherUsersItem = InventoryItem::factory()->for($otherUser)->pokeball($pokeball)->create(['quantity' => 3]);

    new AddInventoryItemAction()->handle($user, $pokeball, 5);

    assertDatabaseCount(InventoryItem::class, 3);

    expect($item->fresh()->quantity)->toBe(15)
        ->and($otherItem->fresh()->quantity)->toBe(4)
        ->and($otherUsersItem->fresh()->quantity)->toBe(3);
});

it('fills a stack up to the limit', function () {
    config(['game.inventory.max_stack' => 50]);

    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $item     = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 45]);

    new AddInventoryItemAction()->handle($user, $pokeball, 5);

    expect($item->fresh()->quantity)->toBe(50);
});

it('refuses to grow a stack over the limit and leaves the quantity unchanged', function () {
    config(['game.inventory.max_stack' => 50]);

    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();
    $item     = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 45]);

    expect(fn () => new AddInventoryItemAction()->handle($user, $pokeball, 6))
        ->toThrow(InventoryStackFullException::class);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($item->fresh()->quantity)->toBe(45);
});

it('refuses a new stack over the limit without creating it', function () {
    config(['game.inventory.max_stack' => 50]);

    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();

    expect(fn () => new AddInventoryItemAction()->handle($user, $pokeball, 51))
        ->toThrow(InventoryStackFullException::class);

    assertDatabaseCount(InventoryItem::class, 0);
});

it('throws for a quantity of zero or below', function (int $quantity) {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();

    expect(fn () => new AddInventoryItemAction()->handle($user, $pokeball, $quantity))
        ->toThrow(InvalidArgumentException::class);

    assertDatabaseCount(InventoryItem::class, 0);
})->with([
    'zero'     => [0],
    'negative' => [-1],
]);
