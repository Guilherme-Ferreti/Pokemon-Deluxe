<?php

declare(strict_types=1);

use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\TmRotation;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the shop page without errors', function () {
    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->withMoney(12450)->create());

    visit(route('shop.web.index'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Shop')
        ->assertNoJavascriptErrors();
});

it('lists the balls with their price and how many the player owns', function () {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create(['name' => 'Ultra Ball', 'price' => 1200]);

    InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 42]);
    TmRotation::factory()->create(['date' => today()]);

    actingAs($user);

    visit(route('shop.web.index'))
        ->assertSeeIn('@shop-items-list', 'Ultra Ball')
        ->assertSeeIn("@shop-item-{$pokeball->id}-row__price-indicator", '₽ 1,200')
        ->assertSeeIn('@shop-items-list', 'You own: 42');
});

it('switches to the tms tab and keeps it in the url', function () {
    $tm = Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam', 'type' => PokemonType::ICE, 'power' => 90]);

    TmRotation::factory()->create(['date' => today(), 'move_ids' => [$tm->id]]);

    actingAs(User::factory()->create());

    visit(route('shop.web.index'))
        ->assertSeeIn('@shop-tabs__tms-button', "Today's TMs")
        ->click('@shop-tabs__tms-button')
        ->assertSeeIn('@shop-items-list', 'TM 395 Ice Beam')
        ->assertSeeIn('@shop-items-list', 'Power 90')
        ->assertQueryStringHas('tab', 'tms')
        ->refresh()
        ->assertSeeIn('@shop-items-list', 'TM 395 Ice Beam')
        ->assertNoJavascriptErrors();
});

it('opens the tab named in the url', function () {
    EvolutionItem::factory()->create(['name' => 'Fire Stone', 'price' => 3000]);
    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->create());

    visit(route('shop.web.index') . '?tab=evolution-items')
        ->assertSeeIn('@shop-items-list', 'Fire Stone');
});

it('buys an item without asking first', function () {
    $user     = User::factory()->withMoney(5000)->create();
    $pokeball = Pokeball::factory()->create(['name' => 'Ultra Ball', 'price' => 1200]);

    TmRotation::factory()->create(['date' => today()]);

    actingAs($user);

    visit(route('shop.web.index'))
        ->click("@shop-item-{$pokeball->id}-buy-form__buy-button")
        ->waitForText('You bought 1 Ultra Ball.')
        ->assertSeeIn("@shop-item-{$pokeball->id}-row", 'You own: 1')
        ->assertNoJavascriptErrors();

    expect($user->fresh()->money)->toBe(3800);
});

it('asks before a purchase over 10,000', function () {
    $user     = User::factory()->withMoney(12450)->create();
    $pokeball = Pokeball::factory()->create(['name' => 'Ultra Ball', 'price' => 1200]);

    TmRotation::factory()->create(['date' => today()]);

    actingAs($user);

    visit(route('shop.web.index'))
        ->type("@shop-item-{$pokeball->id}-buy-form__quantity-input", '10')
        ->assertSeeIn("@shop-item-{$pokeball->id}-buy-form__buy-button", '₽ 12,000')
        ->click("@shop-item-{$pokeball->id}-buy-form__buy-button")
        ->assertSeeIn("@shop-item-{$pokeball->id}-buy-form__confirm-dialog", 'Buy 10 Ultra Balls for ₽ 12,000?')
        ->assertSeeIn("@shop-item-{$pokeball->id}-buy-form__confirm-dialog", "You'll have ₽ 450 left.")
        ->click("@shop-item-{$pokeball->id}-buy-form__confirm-button")
        ->waitForText('You bought 10 Ultra Balls.')
        ->assertSeeIn("@shop-item-{$pokeball->id}-row", 'You own: 10')
        ->assertNoJavascriptErrors();

    expect($user->fresh()->money)->toBe(450);
});

it('disables buying what the player cannot afford', function () {
    $pokeball = Pokeball::factory()->create(['price' => 1200]);

    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->withMoney(450)->create());

    visit(route('shop.web.index'))
        ->assertDisabled("@shop-item-{$pokeball->id}-buy-form__buy-button")
        ->assertNotPresent("@shop-item-{$pokeball->id}-buy-form__stack-full-indicator");
});
