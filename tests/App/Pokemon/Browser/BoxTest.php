<?php

declare(strict_types=1);

use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonTrait;
use Shared\Models\Pokedex;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the box page without errors', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(5)->inBox()->create();

    actingAs($user);

    visit(route('web.pokemon.box'))
        ->assertNoSmoke()
        ->assertSeeIn('@page-title', 'Your Box');
});

it('shows the box empty state when there are no pokemon in the box', function () {
    actingAs(User::factory()->create());

    visit(route('web.pokemon.box'))
        ->assertVisible('@pokemon-box-empty-state');
});

it('shows box pokemon when they exist and hides empty state', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(3)->inBox()->create();

    actingAs($user);

    visit(route('web.pokemon.box'))
        ->assertDontSee('@pokemon-box-empty-state');
});

describe('search filters', function () {
    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box', [
            'search'      => 'Pikachu',
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [PokemonTrait::LEGENDARY],
            'generations' => [Generation::KANTO],
        ]))
            ->assertValue('search', 'Pikachu')
            ->click('@search-filters-button')
            ->assertAttribute('[aria-label="Fire"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Water"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Grass"]', 'data-state', 'unchecked')
            ->assertAttribute('[aria-label="Legendary"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Kanto"]', 'data-state', 'checked');
    });

    it('searches the box when typing in the search bar', function () {
        $user       = User::factory()->create();
        $pikachu    = Pokedex::factory()->create(['name' => 'Pikachu']);
        $charmander = Pokedex::factory()->create(['name' => 'Charmander']);

        Pokemon::factory()->for($user)->for($pikachu)->inBox()->create();
        Pokemon::factory()->for($user)->for($charmander)->inBox()->create();

        actingAs($user);

        visit(route('web.pokemon.box'))
            ->assertSee('Pikachu')
            ->assertSee('Charmander')
            ->type('search', 'Pikachu')
            ->assertSee('Pikachu')
            ->assertDontSee('Charmander')
            ->assertQueryStringHas('search', 'Pikachu');
    });

    it('disables type checkboxes after selecting two types', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->assertDisabled('[aria-label="Grass"]');
    });

    it('disables baby trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Gmax"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables legendary trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables legendary trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables mythical trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mythical trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mega trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Gmax"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables mega trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables gmax trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="Gmax"]');
    });

    it('disables gmax trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Gmax"]');
    });

    it('does not disable shiny trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Shiny"]');
    });

    it('re-enables a disabled trait when the conflicting selection is removed', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Baby"]');
    });
});
