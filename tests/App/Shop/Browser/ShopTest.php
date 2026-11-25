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
        ->assertSeeIn('@shop-items-list', '₽ 1,200')
        ->assertSeeIn('@shop-items-list', 'You own: 42');
});

it('switches to the tms tab and keeps it in the url', function () {
    $tm = Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam', 'type' => PokemonType::ICE, 'power' => 90]);

    TmRotation::factory()->create(['date' => today(), 'move_ids' => [$tm->id]]);

    actingAs(User::factory()->create());

    visit(route('shop.web.index'))
        ->click('@shop-tabs__tms-button')
        ->assertSeeIn('@shop-items-list', 'TM 395 Ice Beam')
        ->assertSeeIn('@shop-items-list', 'Power 90')
        ->assertSeeIn('@tm-rotation-notice', 'New TMs in')
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
