<?php

declare(strict_types=1);

use Shared\Models\EvolutionItem;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('pokemon.web.evolve', Pokemon::factory()->create()), ['speciesEvolutionId' => fake()->uuid()])
        ->assertRedirectToRoute('auth.web.login.create');
});

it("gives 404 for another player's pokemon", function () {
    $evolution = SpeciesEvolution::factory()->create();

    actingAs(User::factory()->create())
        ->post(route('pokemon.web.evolve', Pokemon::factory()->for($evolution->from)->create()), ['speciesEvolutionId' => $evolution->id])
        ->assertNotFound();
});

it('evolves the pokemon and says what it became', function () {
    $user       = User::factory()->create();
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon']);
    $charizard  = Species::factory()->create(['name' => 'Charizard']);
    $evolution  = SpeciesEvolution::factory()->create(['from_species_id' => $charmeleon->id, 'into_species_id' => $charizard->id, 'evolution_item_id' => null, 'min_level' => 36, 'gender' => null]);
    $pokemon    = Pokemon::factory()->for($user)->for($charmeleon)->shiny()->create(['level' => 36]);

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.evolve', $pokemon), ['speciesEvolutionId' => $evolution->id])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'success')
        ->assertInertiaFlash('toast.title', 'Evolved!')
        ->assertInertiaFlash('toast.message', 'Your Shiny Charmeleon evolved into Shiny Charizard.');

    expect($pokemon->fresh()->species_id)->toBe($charizard->id);
});

it('warns when a requirement is not met', function () {
    $user       = User::factory()->create();
    $poliwhirl  = Species::factory()->create();
    $waterStone = EvolutionItem::factory()->create(['name' => 'Water Stone']);
    $evolution  = SpeciesEvolution::factory()->create(['from_species_id' => $poliwhirl->id, 'evolution_item_id' => $waterStone->id, 'min_level' => null, 'gender' => null]);
    $pokemon    = Pokemon::factory()->for($user)->for($poliwhirl)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.evolve', $pokemon), ['speciesEvolutionId' => $evolution->id])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($pokemon->fresh()->species_id)->toBe($poliwhirl->id);
});

it('warns when the evolution is of another species', function () {
    $user      = User::factory()->create();
    $evolution = SpeciesEvolution::factory()->create();
    $pokemon   = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.evolve', $pokemon), ['speciesEvolutionId' => $evolution->id])
        ->assertRedirect(route('pokemon.web.box.show', $pokemon))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($pokemon->fresh()->species_id)->not->toBe($evolution->into_species_id);
});

it('validates the evolution', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();

    actingAs($user)
        ->from(route('pokemon.web.box.show', $pokemon))
        ->post(route('pokemon.web.evolve', $pokemon), ['speciesEvolutionId' => fake()->uuid()])
        ->assertSessionHasErrors(['speciesEvolutionId']);
});
