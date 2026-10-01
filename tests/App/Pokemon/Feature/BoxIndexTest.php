<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('pokemon.web.box.index'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the box page with expected structure', function () {
    $total   = 15;
    $perPage = 12;
    $user    = User::factory()->create();

    $boxPokemon = Pokemon::factory()
        ->for($user)
        ->has(Move::factory()->count(4))
        ->count($total)
        ->inBox()
        ->sequence(fn ($sequence) => ['captured_at' => now()->subDays($total - $sequence->index)])
        ->create();

    $newestPokemon = $boxPokemon->last();

    actingAs($user);

    get(route('pokemon.web.box.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pokemon/pages/Box/Index')
            ->has('activeFilters')
            ->has('filterOptions', fn (Assert $page) => $page->hasAll(['types', 'traits', 'generations', 'sort', 'dir']))
            ->has('pokemonList.data', $perPage)
            ->where('pokemonList.data.0.id', $newestPokemon->id)
            ->where('pokemonList.data.0.name', $newestPokemon->getDisplayName())
            ->where('pokemonList.data.0.gender', $newestPokemon->gender)
            ->where('pokemonList.data.0.frontSprite', $newestPokemon->getDisplayFrontSprite())
            ->where('pokemonList.data.0.level', $newestPokemon->level)
            ->where('pokemonList.data.0.experience', $newestPokemon->experience)
            ->where('pokemonList.data.0.species.types', Arr::pluck($newestPokemon->species->types, 'value'))
            ->where('pokemonList.data.0.moves.0.name', $newestPokemon->moves[0]->name)
            ->where('pokemonList.data.0.moves.1.name', $newestPokemon->moves[1]->name)
            ->where('pokemonList.data.0.moves.2.name', $newestPokemon->moves[2]->name)
            ->where('pokemonList.data.0.moves.3.name', $newestPokemon->moves[3]->name)
            ->where('pokemonList.total', $total)
            ->where('pokemonList.perPage', $perPage)
            ->where('pokemonList.lastPage', 2)
        );
});

it("only shows the authenticated user's pokemon", function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Pokemon::factory(5)->inBox()->create();

    actingAs($user)
        ->get(route('pokemon.web.box.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('pokemonList.data', 1)
            ->where('pokemonList.data.0.id', $pokemon->id)
        );
});

it('filters box pokemon by search query', function () {
    $user = User::factory()->create();

    $pikachu    = Pokemon::factory()->for($user)->for(Species::factory()->create(['name' => 'Pikachu']))->inBox()->create();
    $charmander = Pokemon::factory()->for($user)->for(Species::factory()->create(['name' => 'Charmander']))->inBox()->create();

    actingAs($user)
        ->get(route('pokemon.web.box.index', ['search' => 'Pikachu']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('pokemonList.data', 1)
            ->where('pokemonList.data.0.id', $pikachu->id)
        );
});

it('excludes party pokemon from the box', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->get(route('pokemon.web.box.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('pokemonList.data', 0)
        );
});
