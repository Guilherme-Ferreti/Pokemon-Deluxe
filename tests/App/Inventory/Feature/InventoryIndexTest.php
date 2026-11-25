<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('inventory.web.index'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the inventory page with expected structure', function () {
    $user          = User::factory()->create();
    $pokeball      = Pokeball::factory()->create(['name' => 'Ultra Ball', 'description' => 'Catches with 2x.', 'sprite' => 'https://example.com/ultra-ball.png', 'price' => 1200]);
    $evolutionItem = EvolutionItem::factory()->create(['name' => 'Fire Stone', 'description' => 'Evolves Vulpix.', 'sprite' => 'https://example.com/fire-stone.png', 'price' => 3000]);
    $tm            = Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam', 'type' => PokemonType::ICE, 'power' => 90]);
    $boostStone    = SpecialItem::factory()->boostStone()->create(['description' => 'May raise a boost.', 'sprite' => null]);
    $xpShare       = SpecialItem::factory()->xpShare()->create(['description' => 'Shares XP.', 'sprite' => 'https://example.com/exp-share.png']);

    $pokeballStack      = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 42]);
    $evolutionItemStack = InventoryItem::factory()->for($user)->evolutionItem($evolutionItem)->create(['quantity' => 2]);
    $tmStack            = InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]);
    $boostStoneStack    = InventoryItem::factory()->for($user)->specialItem($boostStone)->create(['quantity' => 37]);
    $xpShareStack       = InventoryItem::factory()->for($user)->specialItem($xpShare)->create(['quantity' => 1]);

    actingAs($user);

    get(route('inventory.web.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Inventory/pages/Inventory/Index/index')
            ->where('pokeballs', [[
                'id'          => $pokeballStack->id,
                'name'        => 'Ultra Ball',
                'sprite'      => 'https://example.com/ultra-ball.png',
                'description' => 'Catches with 2x.',
                'quantity'    => 42,
            ]])
            ->where('evolutionItems', [[
                'id'          => $evolutionItemStack->id,
                'name'        => 'Fire Stone',
                'sprite'      => 'https://example.com/fire-stone.png',
                'description' => 'Evolves Vulpix.',
                'quantity'    => 2,
            ]])
            ->where('tms', [[
                'id'          => $tmStack->id,
                'name'        => 'TM 395 Ice Beam',
                'sprite'      => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/tm-ice.png',
                'description' => 'Teaches Ice Beam, an Ice-type move with 90 power.',
                'quantity'    => 1,
            ]])
            ->where('boostStones', [[
                'id'          => $boostStoneStack->id,
                'name'        => 'Boost Stone',
                'sprite'      => null,
                'description' => 'May raise a boost.',
                'quantity'    => 37,
            ]])
            ->where('keyItems', [[
                'id'          => $xpShareStack->id,
                'name'        => 'XP Share',
                'sprite'      => 'https://example.com/exp-share.png',
                'description' => 'Shares XP.',
                'quantity'    => 1,
            ]])
        );
});

it('has empty groups for a player without items', function () {
    actingAs(User::factory()->create());

    get(route('inventory.web.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pokeballs', [])
            ->where('evolutionItems', [])
            ->where('tms', [])
            ->where('boostStones', [])
            ->where('keyItems', [])
        );
});

it('sorts tms by code and other items by name', function () {
    $user = User::factory()->create();

    InventoryItem::factory()->for($user)->pokeball(Pokeball::factory()->create(['name' => 'Ultra Ball']))->create();
    InventoryItem::factory()->for($user)->pokeball(Pokeball::factory()->create(['name' => 'Great Ball']))->create();
    InventoryItem::factory()->for($user)->tm(Move::factory()->plainDamage()->create(['tm_code' => 'TM399', 'name' => 'Thunderbolt']))->create();
    InventoryItem::factory()->for($user)->tm(Move::factory()->plainDamage()->create(['tm_code' => 'TM047', 'name' => 'Earthquake']))->create();

    actingAs($user);

    get(route('inventory.web.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pokeballs.0.name', 'Great Ball')
            ->where('pokeballs.1.name', 'Ultra Ball')
            ->where('tms.0.name', 'TM 047 Earthquake')
            ->where('tms.1.name', 'TM 399 Thunderbolt')
        );
});

it('lists only the player\'s own stacks that still hold items', function () {
    $user     = User::factory()->create();
    $pokeball = Pokeball::factory()->create();

    InventoryItem::factory()->for($user)->pokeball(Pokeball::factory()->create())->create(['quantity' => 0]);
    InventoryItem::factory()->pokeball($pokeball)->create(['quantity' => 5]);

    actingAs($user);

    get(route('inventory.web.index'))
        ->assertInertia(fn (Assert $page) => $page->where('pokeballs', []));
});
