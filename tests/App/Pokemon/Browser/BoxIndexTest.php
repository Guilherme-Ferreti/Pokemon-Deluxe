<?php

declare(strict_types=1);

use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonTrait;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the box page without errors', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(5)->inBox()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->assertNoSmoke()
        ->assertSeeIn('@page-header__title', 'Your Box');
});

it('shows box pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->for(Species::factory()->dualType())->has(Move::factory()->count(4))->inBox()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->assertSee($pokemon->species->name)
        ->assertSee($pokemon->species->types[0]->label())
        ->assertSee(($pokemon->species->types[1] ?? null)?->label())
        ->assertSee($pokemon->species->name)
        ->assertSee($pokemon->gender->label())
        ->assertSee($pokemon->level)
        ->assertSee($pokemon->experience)
        ->assertSee($pokemon->moves[0]->name)
        ->assertSee($pokemon->moves[1]->name)
        ->assertSee($pokemon->moves[2]->name)
        ->assertSee($pokemon->moves[3]->name)
        ->assertDontSee('@pokemon-box__empty-state');
});

it('shows the box empty state when there are no pokemon in the box', function () {
    actingAs(User::factory()->create());

    visit(route('pokemon.web.box.index'))
        ->assertVisible('@pokemon-box__empty-state');
});

describe('search filters', function () {
    it('searches the box when typing in the search bar', function () {
        $user       = User::factory()->create();
        $pikachu    = Species::factory()->create(['name' => 'Pikachu']);
        $charmander = Species::factory()->create(['name' => 'Charmander']);

        Pokemon::factory()->for($user)->for($pikachu)->inBox()->create();
        Pokemon::factory()->for($user)->for($charmander)->inBox()->create();

        actingAs($user);

        visit(route('pokemon.web.box.index'))
            ->assertSee('Pikachu')
            ->assertSee('Charmander')
            ->type('search', 'Pikachu')
            ->assertSee('Pikachu')
            ->assertDontSee('Charmander')
            ->assertQueryStringHas('search', 'Pikachu');
    });

    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index', [
            'search'      => 'Pikachu',
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [PokemonTrait::LEGENDARY],
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

        visit(route('pokemon.web.box.index', [
            'types'       => [ElementalType::FIRE, ElementalType::WATER],
            'traits'      => [PokemonTrait::LEGENDARY],
            'generations' => [Generation::I],
        ]))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('@search-filters-dialog__reset-filters-button')
            ->assertQueryStringMissing('types')
            ->assertQueryStringMissing('traits')
            ->assertQueryStringMissing('generations')
            ->assertDontSee('@search-filters-dialog__active-filters-indicator');
    });

    it('applies filters only after Apply is clicked and discards them when the dialog is closed', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->click('@search-filters-dialog__close-filters-button')
            ->assertQueryStringMissing('types')
            ->assertDontSee('@search-filters-dialog__active-filters-indicator')
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

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Fire"]')
            ->click('[aria-label="Water"]')
            ->assertDisabled('[aria-label="Grass"]');
    });

    it('disables baby trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables baby trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Baby"]');
    });

    it('disables legendary trait when mythical is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mythical"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables legendary trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Legendary"]');
    });

    it('disables mythical trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mythical trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mythical"]');
    });

    it('disables mega trait when gmax is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="G-Max"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables mega trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="Mega"]');
    });

    it('disables gmax trait when mega is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Mega"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('disables gmax trait when baby is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Baby"]')
            ->assertDisabled('[aria-label="G-Max"]');
    });

    it('does not disable shiny trait when legendary is selected', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Shiny"]');
    });

    it('re-enables a disabled trait when the conflicting selection is removed', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index'))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('[aria-label="Legendary"]')
            ->assertDisabled('[aria-label="Baby"]')
            ->click('[aria-label="Legendary"]')
            ->assertEnabled('[aria-label="Baby"]');
    });
});

it('navigates to the pokedex show page from the pokemon dropdown', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->assertSee($pokemon->getDisplayName())
        ->click('@pokemon-box-card__open-dropdown-menu-button')
        ->click('@pokemon-box-card__view-species-link')
        ->assertPathIs(route('dex.web.pokedex.show', $pokemon->species, absolute: false))
        ->assertNoSmoke();
});
