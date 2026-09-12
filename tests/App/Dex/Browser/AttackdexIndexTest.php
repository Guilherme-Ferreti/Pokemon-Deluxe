<?php

declare(strict_types=1);

use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\MoveTrait;
use Shared\Models\Move;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the attackdex page without errors', function () {
    $user = User::factory()->create();
    Move::factory()->count(5)->create();

    actingAs($user);

    visit(route('dex.web.attackdex.index'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Attackdex');
});

it('shows moves', function () {
    $user = User::factory()->create();

    Move::factory()->create(['name' => 'Tackle']);
    Move::factory()->create(['name' => 'Ember']);

    actingAs($user);

    visit(route('dex.web.attackdex.index'))
        ->assertSee('Tackle')
        ->assertSee('Ember')
        ->assertDontSee('@attackdex__empty-state');
});

it('shows the empty state when there are no moves', function () {
    actingAs(User::factory()->create());

    visit(route('dex.web.attackdex.index'))
        ->assertVisible('@attackdex__empty-state');
});

describe('search filters', function () {
    it('searches moves when typing in the search bar', function () {
        $user = User::factory()->create();

        Move::factory()->create(['name' => 'Tackle']);
        Move::factory()->create(['name' => 'Ember']);

        actingAs($user);

        visit(route('dex.web.attackdex.index'))
            ->assertSee('Tackle')
            ->assertSee('Ember')
            ->type('search', 'Tackle')
            ->assertSee('Tackle')
            ->assertDontSee('Ember')
            ->assertQueryStringHas('search', 'Tackle');
    });

    it('shows active filters', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.attackdex.index', [
            'search'   => 'Tackle',
            'types'    => [ElementalType::FIRE, ElementalType::WATER],
            'ailments' => [Ailment::BURN, Ailment::FREEZE],
            'traits'   => [MoveTrait::Z_MOVE],
        ]))
            ->assertValue('search', 'Tackle')
            ->assertSeeIn('@search-filters-dialog__active-filters-indicator', '5')
            ->click('@search-filters-dialog__open-filters-button')
            ->assertAttribute('[aria-label="Fire"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Water"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Grass"]', 'data-state', 'unchecked')
            ->assertAttribute('[aria-label="Burn"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Freeze"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Z-Move"]', 'data-state', 'checked')
            ->assertAttribute('[aria-label="Confusion"]', 'data-state', 'unchecked');
    });

    it('clears active filters when the Reset button is clicked', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.attackdex.index', [
            'types'    => [ElementalType::FIRE, ElementalType::WATER],
            'ailments' => [Ailment::BURN, Ailment::FREEZE],
            'traits'   => [MoveTrait::Z_MOVE],
        ]))
            ->click('@search-filters-dialog__open-filters-button')
            ->click('@search-filters-dialog__reset-filters-button')
            ->assertQueryStringMissing('types')
            ->assertQueryStringMissing('ailments')
            ->assertQueryStringMissing('traits')
            ->assertDontSee('@search-filters-dialog__active-filters-indicator');
    });

    it('applies filters only after Apply is clicked and discards them when the dialog is closed', function () {
        actingAs(User::factory()->create());

        visit(route('dex.web.attackdex.index'))
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

    it('has the correct sort URL in the power column header', function () {
        $user = User::factory()->create();
        Move::factory()->count(3)->create();

        actingAs($user);

        visit(route('dex.web.attackdex.index'))
            ->assertAttribute('[aria-label="Sort by power"]', 'href', '/attackdex?sort=power&dir=asc')
            ->click('[aria-label="Sort by power"]')
            ->assertQueryStringHas('sort', 'power')
            ->assertQueryStringHas('dir', 'asc')
            ->assertAttribute('[aria-label="Sort by power"]', 'href', '/attackdex?sort=power&dir=desc')
            ->click('[aria-label="Sort by power"]')
            ->assertQueryStringHas('sort', 'power')
            ->assertQueryStringHas('dir', 'desc')
            ->assertAttribute('[aria-label="Sort by power"]', 'href', '/attackdex?sort=name&dir=asc')
            ->click('[aria-label="Sort by power"]')
            ->assertQueryStringHas('sort', 'name')
            ->assertQueryStringHas('dir', 'asc');
    });
});
