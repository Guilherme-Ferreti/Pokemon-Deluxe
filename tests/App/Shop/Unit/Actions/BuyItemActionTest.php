<?php

declare(strict_types=1);

use App\Economy\Exceptions\NotEnoughMoneyException;
use App\Inventory\Exceptions\InventoryStackFullException;
use App\Shop\Actions\BuyItemAction;
use App\Shop\Exceptions\ItemNotForSaleException;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\TmRotation;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('buys one item', function () {
    $user     = User::factory()->withMoney(1000)->create();
    $pokeball = Pokeball::factory()->create(['price' => 200]);

    new BuyItemAction()->handle($user, $pokeball, 1);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($user->fresh()->money)->toBe(800)
        ->and(InventoryItem::query()->sole()->quantity)->toBe(1);
});

it('buys many items onto an existing stack', function () {
    $user          = User::factory()->withMoney(20000)->create();
    $evolutionItem = EvolutionItem::factory()->create(['price' => 3000]);
    $stack         = InventoryItem::factory()->for($user)->evolutionItem($evolutionItem)->create(['quantity' => 2]);

    new BuyItemAction()->handle($user, $evolutionItem, 5);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($user->fresh()->money)->toBe(5000)
        ->and($stack->fresh()->quantity)->toBe(7);
});

it('buys a tm from today\'s rotation at its price', function () {
    $user = User::factory()->withMoney(5000)->create();
    $tm   = Move::factory()->plainDamage()->create(['power' => 40]);
    TmRotation::factory()->create(['date' => today(), 'move_ids' => [$tm->id]]);

    new BuyItemAction()->handle($user, $tm, 2);

    assertDatabaseCount(InventoryItem::class, 1);

    expect($user->fresh()->money)->toBe(1000)
        ->and(InventoryItem::query()->sole()->quantity)->toBe(2);
});

it('refuses an item that is not sold in the shop', function (Closure $makeItem, string $message) {
    $user = User::factory()->withMoney(100000)->create();
    $item = $makeItem();

    expect(fn () => new BuyItemAction()->handle($user, $item, 1))
        ->toThrow(ItemNotForSaleException::class, $message);

    assertDatabaseCount(InventoryItem::class, 0);

    expect($user->fresh()->money)->toBe(100000);
})->with([
    'earned-only ball'            => [fn () => Pokeball::factory()->create(['name' => 'Master Ball', 'price' => null]), "Master Ball isn't sold in the Shop."],
    'mega stone'                  => [fn () => EvolutionItem::factory()->create(['name' => 'Gengarite', 'price' => null]), "Gengarite isn't sold in the Shop."],
    'z-move tm'                   => [fn () => Move::factory()->plainDamage()->create(['tm_code' => 'TM700', 'name' => 'Breakneck Blitz', 'is_z_move' => true]), "TM 700 Breakneck Blitz isn't sold in the Shop."],
    'tm not in today\'s rotation' => [function () {
        TmRotation::factory()->create(['date' => today()]);

        return Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam']);
    }, "TM 395 Ice Beam isn't for sale today."],
]);

it('refuses when the player cannot afford it', function () {
    $user     = User::factory()->withMoney(500)->create();
    $pokeball = Pokeball::factory()->create(['price' => 200]);

    expect(fn () => new BuyItemAction()->handle($user, $pokeball, 3))
        ->toThrow(NotEnoughMoneyException::class, 'You need ₽ 600 but only have ₽ 500.');

    assertDatabaseCount(InventoryItem::class, 0);

    expect($user->fresh()->money)->toBe(500);
});

it('refuses when the stack has no room', function () {
    $user     = User::factory()->withMoney(10000)->create();
    $pokeball = Pokeball::factory()->create(['name' => 'Poké Ball', 'price' => 200]);
    $stack    = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 9998]);

    expect(fn () => new BuyItemAction()->handle($user, $pokeball, 2))
        ->toThrow(InventoryStackFullException::class, 'You cannot hold more than 9,999 Poké Ball.');

    assertDatabaseCount(InventoryItem::class, 1);

    expect($user->fresh()->money)->toBe(10000)
        ->and($stack->fresh()->quantity)->toBe(9998);
});

it('refuses a quantity below one', function (int $quantity) {
    $user     = User::factory()->withMoney(1000)->create();
    $pokeball = Pokeball::factory()->create(['price' => 200]);

    expect(fn () => new BuyItemAction()->handle($user, $pokeball, $quantity))
        ->toThrow(InvalidArgumentException::class);

    assertDatabaseCount(InventoryItem::class, 0);

    expect($user->fresh()->money)->toBe(1000);
})->with([0, -1]);
