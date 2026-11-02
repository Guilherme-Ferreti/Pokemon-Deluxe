<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('dex.web.pokedex.index'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the pokedex page with expected structure', function () {
    $user = User::factory()->create();

    $speciesA = Species::factory()->create();

    $speciesB = Species::factory()->create([
        'pokedex_number' => $speciesA->pokedex_number + 1,
    ]);

    Pokemon::factory()->for($user)->for($speciesB)->regular()->inBox()->create();

    actingAs($user);

    get(route('dex.web.pokedex.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dex/pages/Pokedex/Index/index')
            ->has('filters.active')
            ->has('filters.options', fn (Assert $page) => $page->hasAll(['types', 'traits', 'generations']))
            ->has('speciesList.data', 2)
            ->where('speciesList.data.0.id', $speciesA->id)
            ->where('speciesList.data.0.pokedexNumber', $speciesA->pokedex_number)
            ->where('speciesList.data.0.portrait', $speciesA->portraits->regular)
            ->where('speciesList.data.0.name', $speciesA->name)
            ->where('speciesList.data.0.types', Arr::pluck($speciesA->types, 'value'))
            ->where('speciesList.data.0.generation', $speciesA->generation)
            ->where('speciesList.data.0.slug', $speciesA->slug)
            ->where('speciesList.data.0.regularInBox', false)
            ->where('speciesList.data.0.shinyInBox', false)
            ->where('speciesList.data.1.id', $speciesB->id)
            ->where('speciesList.data.1.pokedexNumber', $speciesB->pokedex_number)
            ->where('speciesList.data.1.portrait', $speciesB->portraits->regular)
            ->where('speciesList.data.1.name', $speciesB->name)
            ->where('speciesList.data.1.types', Arr::pluck($speciesB->types, 'value'))
            ->where('speciesList.data.1.generation', $speciesB->generation)
            ->where('speciesList.data.1.slug', $speciesB->slug)
            ->where('speciesList.data.1.regularInBox', true)
            ->where('speciesList.data.1.shinyInBox', false)
            ->where('speciesList.total', 2)
            ->where('speciesList.perPage', 50)
            ->where('speciesList.lastPage', 1)
        );
});

it('shows all species regardless of user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Species::factory()->count(3)->create();

    actingAs($user1)
        ->get(route('dex.web.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 3)
        );

    actingAs($user2)
        ->get(route('dex.web.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 3)
        );
});

it('filters species by search query', function () {
    $user = User::factory()->create();

    Species::factory()->create(['name' => 'Pikachu']);
    Species::factory()->create(['name' => 'Charmander']);

    actingAs($user)
        ->get(route('dex.web.pokedex.index', ['search' => 'Pikachu']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 1)
            ->where('speciesList.data.0.name', 'Pikachu')
        );
});

it('indicates box presence for authenticated user', function () {
    $user = User::factory()->create();

    $speciesA = Species::factory()->create(['pokedex_number' => 1]);
    $speciesB = Species::factory()->create(['pokedex_number' => 2]);

    Pokemon::factory()->for($user)->for($speciesA)->regular()->inBox()->create();
    Pokemon::factory()->for($user)->for($speciesB)->shiny()->inBox()->create();

    actingAs($user);

    get(route('dex.web.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 2)
            ->where('speciesList.data.0.regularInBox', true)
            ->where('speciesList.data.0.shinyInBox', false)
            ->where('speciesList.data.1.regularInBox', false)
            ->where('speciesList.data.1.shinyInBox', true)
        );
});

it('shows no box presence when user has no pokemon of that species', function () {
    $user = User::factory()->create();

    Species::factory()->create(['name' => 'Pikachu']);

    actingAs($user);

    get(route('dex.web.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 1)
            ->where('speciesList.data.0.regularInBox', false)
            ->where('speciesList.data.0.shinyInBox', false)
        );
});
