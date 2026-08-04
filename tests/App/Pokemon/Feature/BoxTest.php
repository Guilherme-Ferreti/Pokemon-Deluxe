<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Pokedex;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('web.pokemon.box'))
        ->assertRedirectToRoute('web.auth.login.create');
});

it('loads the box page with expected structure', function () {
    $total   = 15;
    $perPage = 12;
    $user    = User::factory()->create();

    $boxPokemon = Pokemon::factory()
        ->for($user)
        ->count($total)
        ->inBox()
        ->sequence(fn ($sequence) => ['captured_at' => now()->subDays($total - $sequence->index)])
        ->create();

    $newestPokemon = $boxPokemon->last();

    actingAs($user);

    get(route('web.pokemon.box'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pokemon/Box')
            ->has('searchFilters')
            ->has('filterOptions', fn (Assert $page) => $page->hasAll(['types', 'traits', 'generations', 'sort', 'dir']))
            ->has('box.data', $perPage)
            ->where('box.data.0.id', $newestPokemon->id)
            ->where('box.data.0.name', $newestPokemon->getName())
            ->where('box.data.0.frontSprite', $newestPokemon->getFrontSprite())
            ->where('box.data.0.level', $newestPokemon->level)
            ->where('box.data.0.experience', $newestPokemon->experience)
            ->where('box.data.0.pokedex.types', $newestPokemon->pokedex->types->pluck('value'))
            ->where('box.data.0.moves.0.name', 'Giga Impact')
            ->where('box.data.0.moves.1.name', 'Flamethrower')
            ->where('box.data.0.moves.2.name', 'Earthquake')
            ->where('box.data.0.moves.3.name', 'Ice Beam')
            ->where('box.total', $total)
            ->where('box.perPage', $perPage)
            ->where('box.lastPage', 2)
        );
});

it("only shows the authenticated user's pokemon", function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Pokemon::factory(5)->inBox()->create();

    actingAs($user)
        ->get(route('web.pokemon.box'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('box.data', 1)
            ->where('box.data.0.id', $pokemon->id)
        );
});

it('filters box pokemon by search query', function () {
    $user       = User::factory()->create();
    $pikachu    = Pokedex::factory()->create(['name' => 'Pikachu']);
    $charmander = Pokedex::factory()->create(['name' => 'Charmander']);

    Pokemon::factory()->for($user)->for($pikachu)->inBox()->create();
    Pokemon::factory()->for($user)->for($charmander)->inBox()->create();

    actingAs($user)
        ->get(route('web.pokemon.box', ['search' => 'Pikachu']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('box.data', 1)
            ->where('box.data.0.name', 'Pikachu')
        );
});

it('excludes party pokemon from the box', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user)
        ->get(route('web.pokemon.box'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('box.data', 0)
        );
});
