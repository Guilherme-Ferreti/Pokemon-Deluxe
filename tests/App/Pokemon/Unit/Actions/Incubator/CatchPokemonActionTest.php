<?php

declare(strict_types=1);

use App\Inventory\Exceptions\InsufficientInventoryException;
use App\Pokemon\Actions\Incubator\CatchPokemonAction;
use App\Pokemon\Events\PokemonCaught;
use App\Pokemon\Exceptions\IncubatorEggNotReadyToCatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use Illuminate\Support\Facades\Event;
use Shared\Models\Incubation;
use Shared\Models\InventoryItem;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

it('catches a ready-to-catch egg and adds the pokemon to the user', function () {
    travelTo('2026-01-01 12:00:00');

    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $species  = Species::factory()->withBaseMoves()->create();
    $pokeball = Pokeball::factory()->create();

    InventoryItem::factory()->for($user)->pokeball($pokeball)->create();

    $incubation = Incubation::factory()->for($user)->for($species)->readyToCatch()->create();

    $pokemon = new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball);

    assertDatabaseCount(Pokemon::class, 1);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => '2026-01-01 12:00:00',
    ]);

    assertDatabaseHas(Pokemon::class, [
        'id'               => $pokemon->id,
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
});

it('announces the caught pokemon to other domains', function () {
    Event::fake([PokemonCaught::class]);

    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $pokeball = Pokeball::factory()->create();

    InventoryItem::factory()->for($user)->pokeball($pokeball)->create();
    Incubation::factory()->for($user)->for(Species::factory()->withBaseMoves())->readyToCatch()->create();

    $pokemon = new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball);

    Event::assertDispatchedTimes(PokemonCaught::class, 1);
    Event::assertDispatched(
        PokemonCaught::class,
        fn (PokemonCaught $event) => $event->pokemonId === $pokemon->id && $event->userId === $user->id,
    );
});

it('does not catch an egg when the incubator has no active incubation', function () {
    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $pokeball = Pokeball::factory()->create();

    expect(fn () => new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball))
        ->toThrow(IncubatorNotActiveException::class);

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 0);
});

it('does not catch or announce the pokemon when the user does not own the pokeball', function () {
    Event::fake([PokemonCaught::class]);

    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $pokeball = Pokeball::factory()->create();

    $incubation = Incubation::factory()->for($user)->for(Species::factory()->withBaseMoves())->readyToCatch()->create();

    expect(fn () => new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball))
        ->toThrow(InsufficientInventoryException::class);

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => null,
    ]);

    Event::assertNotDispatched(PokemonCaught::class);
});

it('does not catch an egg that is not ready to catch', function (string $state) {
    Event::fake([PokemonCaught::class]);

    $user     = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);
    $pokeball = Pokeball::factory()->create();

    $incubation = Incubation::factory()->for($user)->{$state}()->create();

    expect(fn () => new CatchPokemonAction()->handle($user, new IncubatorIndex(1), $pokeball))
        ->toThrow(IncubatorEggNotReadyToCatchException::class);

    assertDatabaseCount(Pokemon::class, 0);
    assertDatabaseCount(Incubation::class, 1);

    assertDatabaseHas(Incubation::class, [
        'id'        => $incubation->id,
        'caught_at' => null,
    ]);

    Event::assertNotDispatched(PokemonCaught::class);
})->with([
    'incubating egg'     => ['incubating'],
    'egg ready to hatch' => ['readyToHatch'],
]);
