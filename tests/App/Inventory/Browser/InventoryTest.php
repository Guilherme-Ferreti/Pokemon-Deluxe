<?php

declare(strict_types=1);

use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\TmRotation;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the inventory page without errors', function () {
    $user = User::factory()->create();

    InventoryItem::factory()->for($user)->pokeball(Pokeball::factory()->create(['name' => 'Ultra Ball']))->create(['quantity' => 42]);

    actingAs($user);

    visit(route('inventory.web.index'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Inventory')
        ->assertNoJavascriptErrors();
});

it('shows an item\'s details with its sell price', function () {
    $user  = User::factory()->create();
    $stack = InventoryItem::factory()->for($user)->pokeball(Pokeball::factory()->create(['name' => 'Ultra Ball', 'description' => 'Catches with 2x.', 'price' => 1200]))->create(['quantity' => 42]);

    actingAs($user);

    visit(route('inventory.web.index'))
        ->assertSeeIn("@inventory-item-{$stack->id}-tile", 'Ultra Ball')
        ->assertSeeIn("@inventory-item-{$stack->id}-tile", 'x42')
        ->click("@inventory-item-{$stack->id}-tile")
        ->assertSeeIn('@inventory-item-dialog', 'Catches with 2x.')
        ->assertSeeIn('@inventory-item-dialog', 'You own 42')
        ->assertDontSeeIn('@inventory-item-dialog', 'Earned only')
        ->assertNoJavascriptErrors();
});

it('says an earned-only item cannot be sold', function () {
    $user  = User::factory()->create();
    $stack = InventoryItem::factory()->for($user)->pokeball(Pokeball::factory()->create(['name' => 'Master Ball', 'price' => null]))->create(['quantity' => 1]);

    actingAs($user);

    visit(route('inventory.web.index'))
        ->click("@inventory-item-{$stack->id}-tile")
        ->assertSeeIn('@inventory-item-dialog', 'Earned only')
        ->assertSeeIn('@inventory-item-dialog', "Can't be sold to the shop. Trade it instead.");
});

it('says where to get items for an empty tab and keeps the tab in the url', function () {
    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->create());

    visit(route('inventory.web.index'))
        ->click('@inventory-tabs__tms-button')
        ->assertQueryStringHas('tab', 'tms')
        ->assertSeeIn('@inventory-items-grid__empty-state', 'No TMs yet')
        ->click('@inventory-items-grid__shop-link')
        ->assertPathIs(route('shop.web.index', absolute: false))
        ->assertQueryStringHas('tab', 'tms')
        ->assertNoJavascriptErrors();
});
