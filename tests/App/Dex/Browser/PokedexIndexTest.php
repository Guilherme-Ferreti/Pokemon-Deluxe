<?php

declare(strict_types=1);

use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\SpeciesTrait;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the pokedex page without errors', function () {
    $user = User::factory()->create();
    Species::factory()->count(5)->create();

    actingAs($user);

    visit(route('web.dex.pokedex.index'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-title', 'Pokédex');
});

it('shows species', function () {
    $user = User::factory()->create();

    Species::factory()->create(['name' => 'Pikachu']);
    Species::factory()->create(['name' => 'Charmander']);

    actingAs($user);

    visit(route('web.dex.pokedex.index'))
        ->assertSee('Pikachu')
        ->assertSee('Charmander')
        ->assertDontSee('@pokedex-empty-state');
});

it('shows the empty state when there are no species', function () {
    actingAs(User::factory()->create());

    visit(route('web.dex.pokedex.index'))
        ->assertVisible('@pokedex-empty-state');
});

describe('search filters', function () {
    it('searches species when typing in the search bar', function () {
        $user = User::factory()->create();

        Species::factory()->create(['name' => 'Pikachu']);
        Species::factory()->create(['name' => 'Charmander']);

        actingAs($user);

        visit(route('web.dex.pokedex.index'))
            ->assertSee('Pikachu')
            ->assertSee('Charmander')
            ->type('search', 'Pikachu')
            ->assertSee('Pikachu')
            ->assertDontSee('Charmander')
            ->assertQueryStringHas('search', 'Pikachu');
    });

    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index', [
            'search'      => 'Pikachu',
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [SpeciesTrait::LEGENDARY],
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

        visit(route('web.dex.pokedex.index', [
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [SpeciesTrait::LEGENDARY],
            'generations' => [Generation::I],
        ]))
            ->click('@open-search-filters-button')
            ->click('@reset-search-filters-button')
            ->assertQueryStringMissing('types')
            ->assertQueryStringMissing('traits')
            ->assertQueryStringMissing('generations')
            ->assertDontSee('@active-filters-indicator');
    });

    it('applies filters only after Apply is clicked and discards them when the dialog is closed', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->click('@close-search-filters-button')
            ->assertQueryStringMissing('types')
            ->assertDontSee('@active-filters-indicator')
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

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->assertDisabled('[aria-label="Grass"]');
    });

    it('disables baby trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables legendary trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables legendary trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables mythical trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mythical trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mega trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables mega trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables gmax trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('disables gmax trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('re-enables a disabled trait when the conflicting selection is removed', function () {
        actingAs(User::factory()->create());

        visit(route('web.dex.pokedex.index'))
            ->click('@open-search-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Baby"]');
    });

    it('has the correct sort URL in the name column header', function () {
        $user = User::factory()->create();
        Species::factory()->count(3)->create();

        actingAs($user);

        visit(route('web.dex.pokedex.index'))
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

    visit(route('web.dex.pokedex.index'))
        ->assertSee('Pikachu')
        ->click('@view-species-link')
        ->assertPathIs(route('web.dex.pokedex.show', $species, absolute: false))
        ->assertNoSmoke();
});
