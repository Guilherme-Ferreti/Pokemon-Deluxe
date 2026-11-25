<?php

declare(strict_types=1);

use App\Inventory\Exceptions\InsufficientInventoryException;
use App\Shop\Actions\SellItemAction;
use App\Shop\Exceptions\ItemNotSellableException;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('sells one item for half its price', function () {
    $user     = User::factory()->withMoney(100)->create();
    $pokeball = Pokeball::factory()->create(['price' => 200]);
    $stack    = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 3]);

    $earnedMoney = new SellItemAction()->handle($user, $pokeball, 1);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($earnedMoney)->toBe(100)
        ->and($user->fresh()->money)->toBe(200)
        ->and($stack->fresh()->quantity)->toBe(2);
});

it('sells many items at once', function () {
    $user          = User::factory()->withMoney(0)->create();
    $evolutionItem = EvolutionItem::factory()->create(['price' => 3000]);
    $stack         = InventoryItem::factory()->for($user)->evolutionItem($evolutionItem)->create(['quantity' => 4]);

    $earnedMoney = new SellItemAction()->handle($user, $evolutionItem, 4);

    expect($earnedMoney)->toBe(6000)
        ->and($user->fresh()->money)->toBe(6000)
        ->and($stack->fresh()->quantity)->toBe(0);
});

it('sells a tm that is not in today\'s rotation at half its price', function () {
    $user  = User::factory()->withMoney(0)->create();
    $tm    = Move::factory()->plainDamage()->create(['power' => 90]);
    $stack = InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]);

    $earnedMoney = new SellItemAction()->handle($user, $tm, 1);

    expect($earnedMoney)->toBe(5000)
        ->and($user->fresh()->money)->toBe(5000)
        ->and($stack->fresh()->quantity)->toBe(0);
});

it('refuses an earned-only item', function (Closure $makeItem, Closure $makeStack, string $message) {
    $user  = User::factory()->withMoney(100)->create();
    $item  = $makeItem();
    $stack = $makeStack($user, $item);

    expect(fn () => new SellItemAction()->handle($user, $item, 1))
        ->toThrow(ItemNotSellableException::class, $message);

    expect($user->fresh()->money)->toBe(100)
        ->and($stack->fresh()->quantity)->toBe(1);
})->with([
    'master ball' => [
        fn () => Pokeball::factory()->create(['name' => 'Master Ball', 'price' => null]),
        fn (User $user, Pokeball $pokeball) => InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 1]),
        "Master Ball can't be sold to the Shop, only traded.",
    ],
    'z-move tm' => [
        fn () => Move::factory()->plainDamage()->create(['tm_code' => 'TM700', 'name' => 'Breakneck Blitz', 'is_z_move' => true]),
        fn (User $user, Move $tm) => InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]),
        "TM 700 Breakneck Blitz can't be sold to the Shop, only traded.",
    ],
]);

it('refuses to sell more than the player owns', function () {
    $user     = User::factory()->withMoney(100)->create();
    $pokeball = Pokeball::factory()->create(['price' => 200]);
    $stack    = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 2]);

    expect(fn () => new SellItemAction()->handle($user, $pokeball, 3))
        ->toThrow(InsufficientInventoryException::class);

    expect($user->fresh()->money)->toBe(100)
        ->and($stack->fresh()->quantity)->toBe(2);
});

it('refuses a quantity below one', function (int $quantity) {
    $user     = User::factory()->withMoney(100)->create();
    $pokeball = Pokeball::factory()->create(['price' => 200]);
    $stack    = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 2]);

    expect(fn () => new SellItemAction()->handle($user, $pokeball, $quantity))
        ->toThrow(InvalidArgumentException::class);

    expect($user->fresh()->money)->toBe(100)
        ->and($stack->fresh()->quantity)->toBe(2);
})->with([0, -1]);
