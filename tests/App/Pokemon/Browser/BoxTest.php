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

    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box', [
            'search'      => 'Pikachu',
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [PokemonTrait::LEGENDARY],
            'generations' => [Generation::I],
        ]))
            ->assertValue('search', 'Pikachu')
            ->assertSeeIn('@active-filters-indicator', '4')
            ->click('@open-search-filters-button')
            ->assertAttribute('[aria-label="Fire"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Water"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Grass"]', 'data-state', 'unchecked')
            ->assertAttribute('[aria-label="Legendary"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Gen I"]', 'data-state', 'checked');
    });

    it('clears active filters when the Reset button is clicked', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box', [
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [PokemonTrait::LEGENDARY],
            'generations' => [Generation::I],
        ]))
            ->click('@open-search-filters-button')
            ->click('@reset-search-filters-button')
            ->assertQueryStringMissing('types')
            ->assertQueryStringMissing('traits')
            ->assertQueryStringMissing('generations')
            ->assertDontSeeIn('@active-filters-indicator', '4');
    });

    it('applies filters only after Apply is clicked and discards them when the dialog is closed', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->click('@close-search-filters-button')
            ->assertQueryStringMissing('types')
            ->assertDontSeeIn('@active-filters-indicator', '2')
            ->click('@open-search-filters-button')
            ->assertAttribute('[aria-label="Fire"]', 'data-state', 'unchecked')
            ->assertAttribute('[aria-label="Water"]', 'data-state', 'unchecked')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->click('@apply-search-filters-button')
            ->assertQueryStringHas('types')
            ->assertSeeIn('@active-filters-indicator', '2');
    });

    it('disables type checkboxes after selecting two types', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->assertDisabled('[aria-label="Grass"]');
    });

    it('disables baby trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables legendary trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables legendary trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables mythical trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mythical trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mega trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables mega trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables gmax trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('disables gmax trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('does not disable shiny trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Shiny"]');
    });

    it('re-enables a disabled trait when the conflicting selection is removed', function () {
        actingAs(User::factory()->create());

        visit(route('web.pokemon.box'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Baby"]');
    });
});
