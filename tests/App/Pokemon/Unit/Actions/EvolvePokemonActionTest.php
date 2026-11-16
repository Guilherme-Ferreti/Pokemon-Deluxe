<?php

declare(strict_types=1);

use App\Pokemon\Actions\EvolvePokemonAction;
use App\Pokemon\Events\PokemonEvolved;
use App\Pokemon\Exceptions\EvolutionNotAllowedException;
use Illuminate\Support\Facades\Event;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('evolves the pokemon, keeping its level, boost, shine and active moves', function (bool $isMega) {
    Event::fake([PokemonEvolved::class]);

    $tackle     = Move::factory()->create();
    $growl      = Move::factory()->create();
    $waterGun   = Move::factory()->create();
    $from       = Species::factory()->create();
    $into       = Species::factory()->create(['is_mega' => $isMega]);
    $waterStone = EvolutionItem::factory()->create(['name' => 'Water Stone']);

    $into->baseMoves()->attach([$tackle->id, $waterGun->id]);

    $pokemon = Pokemon::factory()->create(['species_id' => $from->id, 'level' => 40, 'boost' => 12, 'is_shiny' => true]);
    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $growl->id => ['slot' => 2]]);

    $inventoryItem = InventoryItem::factory()->for($pokemon->user)->evolutionItem($waterStone)->create(['quantity' => 2]);
    $evolution     = SpeciesEvolution::factory()->create([
        'from_species_id'   => $from->id,
        'into_species_id'   => $into->id,
        'evolution_item_id' => $waterStone->id,
        'min_level'         => 30,
        'gender'            => null,
    ]);

    new EvolvePokemonAction()->handle($pokemon, $evolution);

    $pokemon = $pokemon->fresh();

    expect($pokemon)
        ->species_id->toBe($into->id)
        ->level->toBe(40)
        ->boost->toBe(12)
        ->is_shiny->toBeTrue()
        ->and($inventoryItem->fresh()?->quantity)->toBe(1);

    assertDatabaseCount('pokemon_move', 3);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon?->id, 'move_id' => $tackle->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon?->id, 'move_id' => $growl->id, 'slot' => 2]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon?->id, 'move_id' => $waterGun->id, 'slot' => null]);

    Event::assertDispatchedTimes(PokemonEvolved::class, 1);
    Event::assertDispatched(PokemonEvolved::class, fn (PokemonEvolved $event) => $event->pokemonId === $pokemon?->id
        && $event->userId === $pokemon->user_id
        && $event->fromSpeciesId === $from->id
        && $event->intoSpeciesId === $into->id
        && $event->isMega === $isMega);
})->with([
    'regular evolution' => [false],
    'mega evolution'    => [true],
]);

it('evolves by level alone without an item', function () {
    $from = Species::factory()->create();
    $into = Species::factory()->create(['is_gmax' => true]);

    $pokemon   = Pokemon::factory()->create(['species_id' => $from->id, 'level' => 100]);
    $evolution = SpeciesEvolution::factory()->create([
        'from_species_id'   => $from->id,
        'into_species_id'   => $into->id,
        'evolution_item_id' => null,
        'min_level'         => 100,
        'gender'            => null,
    ]);

    new EvolvePokemonAction()->handle($pokemon, $evolution);

    expect($pokemon->fresh()?->species_id)->toBe($into->id);
});

it('refuses an evolution whose requirements are not met', function (int $level, int $ownedQuantity) {
    Event::fake([PokemonEvolved::class]);

    $from       = Species::factory()->create();
    $waterStone = EvolutionItem::factory()->create(['name' => 'Water Stone']);

    $pokemon       = Pokemon::factory()->create(['species_id' => $from->id, 'level' => $level]);
    $inventoryItem = InventoryItem::factory()->for($pokemon->user)->evolutionItem($waterStone)->create(['quantity' => $ownedQuantity]);
    $evolution     = SpeciesEvolution::factory()->create([
        'from_species_id'   => $from->id,
        'evolution_item_id' => $waterStone->id,
        'min_level'         => 30,
        'gender'            => null,
    ]);

    expect(fn () => new EvolvePokemonAction()->handle($pokemon, $evolution))
        ->toThrow(EvolutionNotAllowedException::class)
        ->and($pokemon->fresh()?->species_id)->toBe($from->id)
        ->and($inventoryItem->fresh()?->quantity)->toBe($ownedQuantity);

    Event::assertNotDispatched(PokemonEvolved::class);
})->with([
    'level too low'   => [29, 1],
    'item not in bag' => [30, 0],
]);

it('refuses an evolution of another species', function () {
    $pokemon   = Pokemon::factory()->create(['level' => 50]);
    $evolution = SpeciesEvolution::factory()->create(['evolution_item_id' => null, 'min_level' => null, 'gender' => null]);

    expect(fn () => new EvolvePokemonAction()->handle($pokemon, $evolution))
        ->toThrow(InvalidArgumentException::class);
});
