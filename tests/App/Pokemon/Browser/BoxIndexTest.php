<?php

declare(strict_types=1);

use App\Pokemon\Enums\PokemonTrait;
use Shared\Enums\Generation;
use Shared\Enums\PokemonType;
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
    $pokemon = Pokemon::factory()->for($user)->for(Species::factory()->dualType())->withActiveMoves()->inBox()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__name", $pokemon->species->name)
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__types", $pokemon->species->types[0]->label())
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__types", ($pokemon->species->types[1] ?? null)?->label())
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__gender", $pokemon->gender->label())
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__level", $pokemon->level)
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__experience", $pokemon->experience)
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__move-1", $pokemon->activeMoves[0]->name)
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__move-2", $pokemon->activeMoves[1]->name)
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__move-3", $pokemon->activeMoves[2]->name)
        ->assertSeeIn("@pokemon-{$pokemon->id}-card__move-4", $pokemon->activeMoves[3]->name)
        ->assertMissing('@pokemon-grid__empty-state');
});

it('shows the box empty state when there are no pokemon in the box', function () {
    actingAs(User::factory()->create());

    visit(route('pokemon.web.box.index'))
        ->assertVisible('@pokemon-grid__empty-state');
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
            ->assertSeeIn('@pokemon-grid', 'Pikachu')
            ->assertSeeIn('@pokemon-grid', 'Charmander')
            ->type('search', 'Pikachu')
            ->assertSeeIn('@pokemon-grid', 'Pikachu')
            ->assertDontSeeIn('@pokemon-grid', 'Charmander')
            ->assertQueryStringHas('search', 'Pikachu');
    });

    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('pokemon.web.box.index', [
            'search'      => 'Pikachu',
            'types'       => [PokemonType::FIRE, PokemonType::WATER],
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
            'types'       => [PokemonType::FIRE, PokemonType::WATER],
            'traits'      => [PokemonTrait::LEGENDARY],
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

        visit(route('pokemon.web.box.index'))
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
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__view-species-link")
        ->assertPathIs(route('dex.web.pokedex.show', $pokemon->species, absolute: false))
        ->assertNoSmoke();
});

it('navigates to the pokemon page from the pokemon dropdown', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__view-pokemon-link")
        ->assertPathIs(route('pokemon.web.box.show', $pokemon, absolute: false))
        ->assertSeeIn('@pokemon-hero__name', $pokemon->getDisplayName())
        ->assertNoSmoke();
});

it('adds a pokemon to the party from its card', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();

    Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->assertMissing("@pokemon-{$pokemon->id}-card__in-party-indicator")
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__add-to-party-button")
        ->wait(1)
        ->assertVisible("@pokemon-{$pokemon->id}-card__in-party-indicator")
        ->assertSee("{$pokemon->getDisplayName()} joined your party.")
        ->assertNoJavascriptErrors();

    expect($pokemon->fresh()->in_party)->toBeTrue();
});

it('swaps a pokemon into a full party from its card', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create(['caught_at' => now()]);
    $leaving = Pokemon::factory()->for($user)->inParty()->create(['caught_at' => now()->subDay()]);

    Pokemon::factory()->for($user)->inParty()->count(5)->create(['caught_at' => now()->subDays(2)]);

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__add-to-party-button")
        ->wait(0.5)
        ->assertSeeIn('@party-swap-dialog', 'Your party is full')
        ->click("@party-swap-dialog__pokemon-{$leaving->id}-radio")
        ->click('@party-swap-dialog__swap-button')
        ->wait(1)
        ->assertVisible("@pokemon-{$pokemon->id}-card__in-party-indicator")
        ->assertMissing("@pokemon-{$leaving->id}-card__in-party-indicator")
        ->assertNoJavascriptErrors();

    expect($pokemon->fresh()->in_party)->toBeTrue()
        ->and($leaving->fresh()->in_party)->toBeFalse();
});

it('removes a pokemon from the party from its card after confirming', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->assertVisible("@pokemon-{$pokemon->id}-card__in-party-indicator")
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__remove-from-party-button")
        ->wait(0.5)
        ->assertSeeIn('@party-remove-dialog', "Remove {$pokemon->getDisplayName()} from your party?")
        ->click('@party-remove-dialog__remove-button')
        ->wait(1)
        ->assertMissing("@pokemon-{$pokemon->id}-card__in-party-indicator")
        ->assertNoJavascriptErrors();

    expect($pokemon->fresh()->in_party)->toBeFalse();
});

it('does not reopen the swap dialog after swapping a pokemon back out of the party', function () {
    $user   = User::factory()->create();
    $first  = Pokemon::factory()->for($user)->inBox()->create(['caught_at' => now()]);
    $party  = Pokemon::factory()->for($user)->inParty()->count(6)->create(['caught_at' => now()->subDay()]);
    $second = $party->first();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->click("@pokemon-{$first->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$first->id}-card__add-to-party-button")
        ->wait(0.5)
        ->click("@party-swap-dialog__pokemon-{$second->id}-radio")
        ->click('@party-swap-dialog__swap-button')
        ->wait(1)
        ->assertMissing('@party-swap-dialog')
        ->click("@pokemon-{$second->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$second->id}-card__add-to-party-button")
        ->wait(0.5)
        ->click("@party-swap-dialog__pokemon-{$first->id}-radio")
        ->click('@party-swap-dialog__swap-button')
        ->wait(1)
        ->assertMissing('@party-swap-dialog')
        ->assertNoJavascriptErrors();

    expect($first->fresh()->in_party)->toBeFalse()
        ->and($second->fresh()->in_party)->toBeTrue();
});

it('does not reopen the remove dialog after adding a removed pokemon back to the party', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.box.index'))
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__remove-from-party-button")
        ->wait(0.5)
        ->click('@party-remove-dialog__remove-button')
        ->wait(1)
        ->click("@pokemon-{$pokemon->id}-card__open-dropdown-menu-button")
        ->click("@pokemon-{$pokemon->id}-card__add-to-party-button")
        ->wait(1)
        ->assertVisible("@pokemon-{$pokemon->id}-card__in-party-indicator")
        ->assertMissing('@party-remove-dialog')
        ->assertNoJavascriptErrors();
});
