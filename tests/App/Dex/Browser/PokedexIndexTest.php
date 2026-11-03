<?php

declare(strict_types=1);

use Shared\Enums\Generation;
use Shared\Enums\PokemonType;
use Shared\Enums\SpeciesTrait;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the pokedex page without errors', function () {
    $user = User::factory()->create();
    Species::factory()->count(5)->create();

    actingAs($user);

    visit(route('dex.web.pokedex.index'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Pokédex');
});

it('lists species', function () {
    $user = User::factory()->create();

    $species = Species::factory()->dualType()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.index'))
        ->assertSeeIn('@species-table__row-1-pokedex-number', $species->pokedex_number)
        ->assertSeeIn('@species-table__row-1-name', $species->name)
        ->assertSeeIn('@species-table__row-1-types', $species->types[0]->label())
        ->assertSeeIn('@species-table__row-1-types', ($species->types[1] ?? null)?->label())
        ->assertSeeIn('@species-table__row-1-generation', $species->generation->label())
        ->assertMissing('@species-table__empty-state');
});

it('shows the empty state when there are no species', function () {
    actingAs(User::factory()->create());

    visit(route('dex.web.pokedex.index'))
        ->assertVisible('@species-table__empty-state');
});

describe('search filters', function () {
    it('searches species when typing in the search bar', function () {
        $user = User::factory()->create();

        Species::factory()->create(['name' => 'Bulbasaur', 'pokedex_number' => 1]);
        Species::factory()->create(['name' => 'Charmander', 'pokedex_number' => 4]);

        actingAs($user);

        visit(route('dex.web.pokedex.index'))
            ->assertSeeIn('@species-table__row-1-name', 'Bulbasaur')
            ->assertSeeIn('@species-table__row-2-name', 'Charmander')
            ->type('search', 'Bulbasaur')
            ->assertSeeIn('@species-table__row-1-name', 'Bulbasaur')
            ->assertMissing('@species-table__row-2-name')
            ->assertQueryStringHas('search', 'Bulbasaur');
    });

    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index', [
            'search'      => 'Pikachu',
            'types'       => [PokemonType::FIRE, PokemonType::WATER],
            'traits'      => [SpeciesTrait::LEGENDARY],
            'generations' => [Generation::I],
        ]))
            ->assertValue('search', 'Pikachu')
            ->assertSeeIn('@search-filters-dialog__active-filters-indicator', '4')
            ->click('@search-filters-dialog__open-filters-button')
            ->assertAttribute('[aria-label="Fire"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Water"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Grass"]', 'data-state', 'unchecked')
            ->assertAttribute('[aria-label="Legendary"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Gen I"]', 'data-state', 'checked');
    });

    it('clears active filters when the Reset button is clicked', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index', [
            'types'       => [PokemonType::FIRE, PokemonType::WATER],
            'traits'      => [SpeciesTrait::LEGENDARY],
            'generations' => [Generation::I],
        ]))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('@search-filters-dialog__reset-filters-button')
            ->assertQueryStringMissing('types')
            ->assertQueryStringMissing('traits')
            ->assertQueryStringMissing('generations');
    });

    it('applies filters only after Apply is clicked and discards them when the dialog is closed', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->click('@search-filters-dialog__close-filters-button')
            ->assertQueryStringMissing('types')
            ->click('@search-filters-dialog__open-filters-button')
            ->assertAttribute('[aria-label="Fire"]', 'data-state', 'unchecked')
            ->assertAttribute('[aria-label="Water"]', 'data-state', 'unchecked')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->click('@search-filters-dialog__apply-filters-button')
            ->assertQueryStringHas('types')
            ->assertSeeIn('@search-filters-dialog__active-filters-indicator', '2');
    });

    it('disables type checkboxes after selecting two types', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->assertDisabled('[aria-label="Grass"]');
    });

    it('disables baby trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables legendary trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables legendary trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables mythical trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mythical trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mega trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables mega trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables gmax trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('disables gmax trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('re-enables a disabled trait when the conflicting selection is removed', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.pokedex.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Baby"]');
    });

    it('has the correct sort URL in the name column header', function () {
        $user = User::factory()->create();
        Species::factory()->count(3)->create();

        actingAs($user);

        visit(route('dex.web.pokedex.index'))
            ->assertAttribute('[aria-label="Sort by name"]', 'href', '/pokedex?sort=name&dir=asc')
            ->click('[aria-label="Sort by name"]')
            ->assertQueryStringHas('sort', 'name')
            ->assertQueryStringHas('dir', 'asc')
            ->assertAttribute('[aria-label="Sort by name"]', 'href', '/pokedex?sort=name&dir=desc')
            ->click('[aria-label="Sort by name"]')
            ->assertQueryStringHas('sort', 'name')
            ->assertQueryStringHas('dir', 'desc')
            ->assertAttribute('[aria-label="Sort by name"]', 'href', '/pokedex?sort=pokedex-number&dir=asc')
            ->click('[aria-label="Sort by name"]')
            ->assertQueryStringHas('sort', 'pokedex-number')
            ->assertQueryStringHas('dir', 'asc');
    });

});

it('navigates to the species show page when View is clicked', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create(['name' => 'Pikachu']);

    actingAs($user);

    visit(route('dex.web.pokedex.index'))
        ->assertSeeIn('@species-table__row-1-name', 'Pikachu')
        ->click("@species-table__view-species-{$species->id}-link")
        ->assertPathIs(route('dex.web.pokedex.show', $species, absolute: false))
        ->assertNoSmoke();
});
