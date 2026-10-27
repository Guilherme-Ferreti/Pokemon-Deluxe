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
        ->assertVisible('@nursery-page-header')
        ->assertVisible('@incubators-list')
        ->assertNoJavascriptErrors();
});

it('allows purchasing a not purchased incubator', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-not-purchased"]')
        ->click('[data-test="incubator-1"] [data-test="incubator-purchase-button"]')
        ->wait(1)
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-idle"]')
        ->assertNoJavascriptErrors();
});

it('allows placing an egg in an idle incubator', function () {
    Lottery::alwaysLose();

    $user    = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species = Species::factory()->create(['egg_obtainable' => true]);

    actingAs($user);

    visit(route('pokemon.web.nursery'))
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-idle"]')
        ->click('[data-test="incubator-1"] [data-test="incubator-place-egg-button"]')
        ->wait(1)
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-incubating"]')
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
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-ready-to-hatch"]')
        ->click('[data-test="incubator-1"] [data-test="incubator-hatch-button"]')
        ->wait(1)
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-ready-to-catch"]')
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-catch-button"]')
        ->assertSee('Pikachu')
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
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-ready-to-catch"]')
        ->click('[data-test="incubator-1"] [data-test="incubator-catch-button"]')
        ->wait(1)
        ->assertVisible('@incubator-catch-pokemon-dialog')
        ->assertSee('Bulbasaur')
        ->click('@incubator-catch-pokemon-pokeball-' . $pokeball->id)
        ->click('@incubator-catch-pokemon-submit')
        ->wait(2)
        ->assertDontSee('@incubator-catch-pokemon-dialog')
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-idle"]')
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
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-incubating"]')
        ->assertDontSee('[data-test="incubator-1"] [data-test="incubator-place-egg-button"]')
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
        ->assertVisible('[data-test="incubator-1"] [data-test="incubator-incubating"]')
        ->assertVisible('[data-test="incubator-2"] [data-test="incubator-ready-to-hatch"]')
        ->assertVisible('[data-test="incubator-3"] [data-test="incubator-idle"]')
        ->assertNoJavascriptErrors();
});
