<?php

declare(strict_types=1);

use Shared\Models\Incubation;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;
use Tests\App\Pokemon\RequestFactories\CatchPokemonRequestFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;
use function Pest\Laravel\travelTo;

it('redirects guests to login', function () {
    post(route('pokemon.web.incubators.catch-pokemon', 1))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('catches a ready-to-catch egg', function () {
    travelTo('2026-01-01 12:00:00');

    $data          = CatchPokemonRequestFactory::new()->create();
    $user          = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species       = Species::factory()->withBaseMoves()->create(['name' => 'Pikachu']);
    $pokeball      = Pokeball::first();
    $inventoryItem = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 1]);

    $incubation = Incubation::factory()->for($user)->for($species)->readyToCatch()->create([
        'is_shiny' => true,
    ]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.catch-pokemon', 1), $data)
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Congratulations!')
        ->assertInertiaFlash('toast.message', 'You caught Shiny Pikachu!');

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => '2026-01-01 12:00:00',
    ]);

    assertDatabaseHas(Pokemon::class, [
        'user_id'          => $user->id,
        'original_user_id' => $user->id,
        'species_id'       => $species->id,
        'level'            => 1,
        'in_party'         => false,
        'is_shiny'         => $incubation->is_shiny,
        'gender'           => $incubation->gender,
        'pokeball_id'      => $pokeball->id,
        'caught_at'        => '2026-01-01 12:00:00',
    ]);

    assertDatabaseHas(InventoryItem::class, [
        'id'       => $inventoryItem->id,
        'quantity' => 0,
    ]);
});

it('rejects a catch without a pokeball', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.catch-pokemon', 1))
        ->assertRedirectBackWithErrors('pokeballId');

    assertDatabaseCount(Pokemon::class, 0);
});

it('rejects a catch with a pokeball that does not exist', function () {
    $data = CatchPokemonRequestFactory::new()->withInvalidPokeball()->create();
    $user = User::factory()->create();

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.catch-pokemon', 1), $data)
        ->assertRedirectBackWithErrors('pokeballId');

    assertDatabaseCount(Pokemon::class, 0);
});

it('does not catch an egg that has not hatched yet', function () {
    $data = CatchPokemonRequestFactory::new()->create();
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    $incubation = Incubation::factory()->for($user)->readyToHatch()->create();

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.catch-pokemon', 1), $data)
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => null,
    ]);
});

it('does not catch an egg when the incubator is not active', function () {
    $data = CatchPokemonRequestFactory::new()->create();
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.catch-pokemon', 1), $data)
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 0);
});

it("does not catch another user's egg", function () {
    $data      = CatchPokemonRequestFactory::new()->create();
    $user      = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $otherUser = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    $incubation = Incubation::factory()->for($otherUser)->readyToCatch()->create();

    actingAs($user)
        ->from(route('pokemon.web.nursery'))
        ->post(route('pokemon.web.incubators.catch-pokemon', 1), $data)
        ->assertRedirect(route('pokemon.web.nursery'))
        ->assertInertiaFlash('toast')
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => null,
    ]);
});
