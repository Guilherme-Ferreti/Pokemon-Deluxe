<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\TmRotation;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('shop.web.index'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the shop page with expected structure', function () {
    $this->freezeTime();

    $user          = User::factory()->create();
    $pokeball      = Pokeball::factory()->create(['name' => 'Ultra Ball', 'description' => 'Catches with 2x.', 'sprite' => 'https://example.com/ultra-ball.png', 'price' => 1200]);
    $evolutionItem = EvolutionItem::factory()->create(['name' => 'Fire Stone', 'description' => 'Evolves Vulpix.', 'sprite' => 'https://example.com/fire-stone.png', 'price' => 3000]);
    $tm            = Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam', 'type' => PokemonType::ICE, 'power' => 90]);

    TmRotation::factory()->create(['date' => today(), 'move_ids' => [$tm->id]]);
    InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 42]);
    InventoryItem::factory()->for($user)->tm($tm)->create(['quantity' => 1]);

    actingAs($user);

    get(route('shop.web.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Shop/pages/Shop/Index/index')
            ->where('pokeballs', [[
                'id'            => $pokeball->id,
                'name'          => 'Ultra Ball',
                'sprite'        => 'https://example.com/ultra-ball.png',
                'effect'        => 'Catches with 2x.',
                'price'         => 1200,
                'ownedQuantity' => 42,
            ]])
            ->where('evolutionItems', [[
                'id'            => $evolutionItem->id,
                'name'          => 'Fire Stone',
                'sprite'        => 'https://example.com/fire-stone.png',
                'effect'        => 'Evolves Vulpix.',
                'price'         => 3000,
                'ownedQuantity' => 0,
            ]])
            ->where('tmRotation.startsAt', today()->toIso8601String())
            ->where('tmRotation.endsAt', today()->addDay()->toIso8601String())
            ->where('tmRotation.tms', [[
                'id'            => $tm->id,
                'name'          => 'TM 395 Ice Beam',
                'sprite'        => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/tm-ice.png',
                'type'          => 'ice',
                'power'         => 90,
                'price'         => 10000,
                'ownedQuantity' => 1,
            ]])
        );
});

it('leaves out earned-only items', function () {
    Pokeball::factory()->create(['name' => 'Master Ball', 'price' => null]);
    EvolutionItem::factory()->create(['name' => 'Gengarite', 'price' => null]);
    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->create());

    get(route('shop.web.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('pokeballs', 0)
            ->has('evolutionItems', 0)
        );
});

it('lists only today\'s tms, sorted by code', function () {
    $yesterdaysTm  = Move::factory()->plainDamage()->create(['tm_code' => 'TM001']);
    $todaysLastTm  = Move::factory()->plainDamage()->create(['tm_code' => 'TM399']);
    $todaysFirstTm = Move::factory()->plainDamage()->create(['tm_code' => 'TM047']);

    TmRotation::factory()->create(['date' => today()->subDay(), 'move_ids' => [$yesterdaysTm->id]]);
    TmRotation::factory()->create(['date' => today(), 'move_ids' => [$todaysLastTm->id, $todaysFirstTm->id]]);

    actingAs(User::factory()->create());

    get(route('shop.web.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('tmRotation.tms', 2)
            ->where('tmRotation.tms.0.id', $todaysFirstTm->id)
            ->where('tmRotation.tms.1.id', $todaysLastTm->id)
        );
});

it('sorts balls and evolution items by price, then name', function () {
    Pokeball::factory()->create(['name' => 'Ultra Ball', 'price' => 1200]);
    Pokeball::factory()->create(['name' => 'Net Ball', 'price' => 1000]);
    Pokeball::factory()->create(['name' => 'Dusk Ball', 'price' => 1000]);
    EvolutionItem::factory()->create(['name' => 'Water Stone', 'price' => 3000]);
    EvolutionItem::factory()->create(['name' => 'Fire Stone', 'price' => 3000]);
    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->create());

    get(route('shop.web.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pokeballs.0.name', 'Dusk Ball')
            ->where('pokeballs.1.name', 'Net Ball')
            ->where('pokeballs.2.name', 'Ultra Ball')
            ->where('evolutionItems.0.name', 'Fire Stone')
            ->where('evolutionItems.1.name', 'Water Stone')
        );
});

it('counts only the player\'s own items', function () {
    $pokeball = Pokeball::factory()->create();

    InventoryItem::factory()->pokeball($pokeball)->create(['quantity' => 5]);
    TmRotation::factory()->create(['date' => today()]);

    actingAs(User::factory()->create());

    get(route('shop.web.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pokeballs.0.ownedQuantity', 0)
        );
});
