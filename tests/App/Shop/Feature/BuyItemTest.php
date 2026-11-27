<?php

declare(strict_types=1);

use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\TmRotation;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('shop.web.buy'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('buys items and says what was bought', function () {
    $user     = User::factory()->withMoney(12450)->create();
    $pokeball = Pokeball::factory()->create(['name' => 'Ultra Ball', 'price' => 1200]);

    actingAs($user)
        ->from(route('shop.web.index'))
        ->post(route('shop.web.buy'), ['itemType' => 'pokeball', 'itemId' => $pokeball->id, 'quantity' => 10])
        ->assertRedirect(route('shop.web.index'))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Purchase complete')
        ->assertInertiaFlash('toast.message', 'You bought 10 Ultra Balls.');

    assertDatabaseCount(InventoryItem::class, 1);

    expect($user->fresh()->money)->toBe(450)
        ->and(InventoryItem::query()->sole()->quantity)->toBe(10);
});

it('names a single item and a tm without a plural', function (Closure $makeItem, string $itemType, string $message) {
    $user = User::factory()->withMoney(20000)->create();
    $item = $makeItem();

    actingAs($user)
        ->from(route('shop.web.index'))
        ->post(route('shop.web.buy'), ['itemType' => $itemType, 'itemId' => $item->id, 'quantity' => 1])
        ->assertInertiaFlash('toast.message', $message);

    assertDatabaseCount(InventoryItem::class, 1);
})->with([
    'evolution item' => [fn () => EvolutionItem::factory()->create(['name' => 'Fire Stone', 'price' => 3000]), 'evolution-item', 'You bought 1 Fire Stone.'],
    'tm'             => [function () {
        $tm = Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam', 'power' => 90]);
        TmRotation::factory()->create(['date' => today(), 'move_ids' => [$tm->id]]);

        return $tm;
    }, 'tm', 'You bought 1 TM 395 Ice Beam.'],
]);

it('refuses a purchase the player cannot afford', function () {
    $user     = User::factory()->withMoney(450)->create();
    $pokeball = Pokeball::factory()->create(['price' => 1200]);

    actingAs($user)
        ->from(route('shop.web.index'))
        ->post(route('shop.web.buy'), ['itemType' => 'pokeball', 'itemId' => $pokeball->id, 'quantity' => 1])
        ->assertRedirect(route('shop.web.index'))
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.message', 'You need ₽ 1,200 but only have ₽ 450.');

    assertDatabaseCount(InventoryItem::class, 0);

    expect($user->fresh()->money)->toBe(450);
});

it('validates the purchase', function (Closure $makeData, array $invalidFields) {
    $user = User::factory()->withMoney(100000)->create();

    actingAs($user)
        ->from(route('shop.web.index'))
        ->post(route('shop.web.buy'), $makeData())
        ->assertRedirect(route('shop.web.index'))
        ->assertSessionHasErrors($invalidFields);

    assertDatabaseCount(InventoryItem::class, 0);
})->with([
    'missing everything'         => [fn () => [], ['itemType', 'itemId', 'quantity']],
    'special items are not sold' => [fn () => ['itemType' => 'special-item', 'itemId' => fake()->uuid(), 'quantity' => 1], ['itemType']],
    'unknown item'               => [fn () => ['itemType' => 'pokeball', 'itemId' => fake()->uuid(), 'quantity' => 1], ['itemId']],
    'item of another type'       => [fn () => ['itemType' => 'tm', 'itemId' => Pokeball::factory()->create()->id, 'quantity' => 1], ['itemId']],
    'zero quantity'              => [fn () => ['itemType' => 'pokeball', 'itemId' => Pokeball::factory()->create()->id, 'quantity' => 0], ['quantity']],
    'more than a stack holds'    => [fn () => ['itemType' => 'pokeball', 'itemId' => Pokeball::factory()->create()->id, 'quantity' => 10000], ['quantity']],
]);
