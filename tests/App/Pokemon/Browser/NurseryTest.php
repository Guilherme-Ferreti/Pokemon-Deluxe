<?php

declare(strict_types=1);

use Illuminate\Support\Lottery;
use Shared\Enums\PokemonGender;
use Shared\Models\Incubation;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\actingAs;

it('loads the nursery page without errors', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Nursery')
        ->assertVisible('@incubators-grid')
        ->assertNoJavascriptErrors();
});

it('allows purchasing a not purchased incubator', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('@incubator-1-card--not-purchased')
        ->click('@incubator-1-card--not-purchased__purchase-button')
        ->wait(1)
        ->assertVisible('@incubator-1-card--idle')
        ->assertNoJavascriptErrors();
});

it('allows placing an egg in an idle incubator', function () {
    Lottery::alwaysLose();

    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create(['egg_obtainable' => true]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('@incubator-1-card--idle')
        ->click('@incubator-1-card--idle__place-egg-button')
        ->wait(1)
        ->assertVisible('@incubator-1-card--incubating')
        ->assertNoJavascriptErrors();
});

it('shows incubating state with countdown', function () {
    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create();

    Incubation::factory()->for($user)->for($species)->incubating()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('@incubator-1-card--incubating')
        ->assertVisible('@incubator-1-card--incubating__countdown')
        ->assertNoJavascriptErrors();
});

it('allows hatching a ready to hatch egg', function () {
    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create(['name' => 'Pikachu']);

    Incubation::factory()->for($user)->for($species)->readyToHatch()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('@incubator-1-card--ready-to-hatch')
        ->click('@incubator-1-card--ready-to-hatch__hatch-button')
        ->wait(1)
        ->assertVisible('@incubator-1-card--ready-to-catch')
        ->assertVisible('@incubator-1-card--ready-to-catch__catch-button')
        ->assertSeeIn('@incubator-1-card--ready-to-catch', 'Pikachu')
        ->assertNoJavascriptErrors();
});

it('allows catching a hatched pokemon through the dialog', function () {
    Lottery::alwaysLose();

    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species  = Species::factory()->create(['name' => 'Bulbasaur']);
    $pokeball = Pokeball::factory()->create(['name' => 'Poke Ball']);

    InventoryItem::factory()->for($user)->for($pokeball, 'item')->create(['quantity' => 5]);

    Incubation::factory()->for($user)->for($species)->readyToCatch()->create([
        'incubator_index' => new IncubatorIndex(1),
        'gender'          => PokemonGender::MALE,
        'is_shiny'        => false,
    ]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('@incubator-1-card--ready-to-catch')
        ->click('@incubator-1-card--ready-to-catch__catch-button')
        ->wait(1)
        ->assertVisible('@incubator-catch-pokemon-dialog')
        ->assertSeeIn('@incubator-catch-pokemon-dialog', 'Bulbasaur')
        ->click("@incubator-catch-pokemon-dialog__pokeball-{$pokeball->id}-radio")
        ->click('@incubator-catch-pokemon-dialog__submit-button')
        ->wait(2)
        ->assertMissing('@incubator-catch-pokemon-dialog')
        ->assertVisible('@incubator-1-card--idle')
        ->assertNoJavascriptErrors();
});

it('shows multiple incubators in different states', function () {
    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1), new IncubatorIndex(2), new IncubatorIndex(3)]]);
    $species = Species::factory()->create();

    Incubation::factory()->for($user)->for($species)->incubating()->create([
        'incubator_index' => new IncubatorIndex(1),
    ]);

    Incubation::factory()->for($user)->for($species)->readyToHatch()->create([
        'incubator_index' => new IncubatorIndex(2),
    ]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('@incubator-1-card--incubating')
        ->assertVisible('@incubator-2-card--ready-to-hatch')
        ->assertVisible('@incubator-3-card--idle')
        ->assertNoJavascriptErrors();
});
