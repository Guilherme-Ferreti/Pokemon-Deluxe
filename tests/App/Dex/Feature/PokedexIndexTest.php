<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('web.dex.pokedex.index'))
        ->assertRedirectToRoute('web.auth.login.create');
});

it('loads the pokedex page with expected structure', function () {
    $user = User::factory()->create();

    $charmander = Species::factory()->create([
        'name'           => 'Charmander',
        'types'          => [ElementalType::FIRE],
        'pokedex_number' => 4,
        'generation'     => Generation::I,
    ]);

    $pikachu = Species::factory()->create([
        'name'           => 'Pikachu',
        'types'          => [ElementalType::ELECTRIC],
        'pokedex_number' => 25,
        'generation'     => Generation::I,
    ]);

    Pokemon::factory()->for($user)->for($pikachu)->inBox()->create();

    actingAs($user);

    get(route('web.dex.pokedex.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dex/Pokedex/Index')
            ->has('activeFilters')
            ->has('filterOptions', fn (Assert $page) => $page->hasAll(['types', 'traits', 'generations']))
            ->has('speciesList.data', 2)
            ->where('speciesList.data.0.id', $charmander->id)
            ->where('speciesList.data.0.name', 'Charmander')
            ->where('speciesList.data.0.pokedexNumber', 4)
            ->where('speciesList.data.0.types', [ElementalType::FIRE->value])
            ->where('speciesList.data.0.generation', 'Gen I')
            ->where('speciesList.data.0.regularInBox', false)
            ->where('speciesList.data.0.shinyInBox', false)
            ->where('speciesList.data.1.id', $pikachu->id)
            ->where('speciesList.data.1.name', 'Pikachu')
            ->where('speciesList.data.1.pokedexNumber', 25)
            ->where('speciesList.data.1.types', [ElementalType::ELECTRIC->value])
            ->where('speciesList.data.1.generation', 'Gen I')
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
        ->get(route('web.dex.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 3)
        );

    actingAs($user2)
        ->get(route('web.dex.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 3)
        );
});

it('filters species by search query', function () {
    $user = User::factory()->create();

    Species::factory()->create(['name' => 'Pikachu']);
    Species::factory()->create(['name' => 'Charmander']);

    actingAs($user)
        ->get(route('web.dex.pokedex.index', ['search' => 'Pikachu']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 1)
            ->where('speciesList.data.0.name', 'Pikachu')
        );
});

it('indicates box presence for authenticated user', function () {
    $user = User::factory()->create();

    $speciesA = Species::factory()->create(['pokedex_number' => 1]);
    $speciesB = Species::factory()->create(['pokedex_number' => 2]);

    Pokemon::factory()->for($user)->for($speciesA)->notShiny()->inBox()->create();
    Pokemon::factory()->for($user)->for($speciesB)->shiny()->inBox()->create();

    actingAs($user);

    get(route('web.dex.pokedex.index'))
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

    get(route('web.dex.pokedex.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('speciesList.data', 1)
            ->where('speciesList.data.0.regularInBox', false)
            ->where('speciesList.data.0.shinyInBox', false)
        );
});
