<?php

declare(strict_types=1);

use Shared\Enums\SpeciesTrait;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the species show page without errors', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertTitleContains($species->name)
        ->assertSee($species->name);
});

it('navigates to previous species', function () {
    $user = User::factory()->create();

    $previousSpecies = Species::factory()->create(['pokedex_number' => 2]);
    $species         = Species::factory()->create(['pokedex_number' => 3]);

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->click('@previous-species-button')
        ->assertPathIs(route('dex.web.pokedex.show', $previousSpecies, absolute: false))
        ->assertSee($previousSpecies->name)
        ->assertNoSmoke();
});

it('navigates to next species', function () {
    $user = User::factory()->create();

    $species     = Species::factory()->create(['pokedex_number' => 1]);
    $nextSpecies = Species::factory()->create(['pokedex_number' => 2]);

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->click('@next-species-button')
        ->assertPathIs(route('dex.web.pokedex.show', $nextSpecies, absolute: false))
        ->assertSee($nextSpecies->name)
        ->assertNoSmoke();
});

it('displays the species hero card', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->assertSeeIn('@pokedex-species-hero', $species->name)
        ->assertSeeIn('@pokedex-species-hero', $species->pokedex_number)
        ->assertSeeIn('@pokedex-species-hero', $species->genus)
        ->assertSeeIn('@pokedex-species-hero', $species->text_entry);
});

describe('metadata card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->dualType()->create([
            'is_baby'      => true,
            'is_legendary' => true,
            'is_mythical'  => true,
            'is_mega'      => true,
            'is_gmax'      => true,
        ]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-metadata', $species->types[0]->label())
            ->assertSeeIn('@pokedex-species-metadata', $species->types[1]->label())
            ->assertSeeIn('@pokedex-species-metadata', $species->generation->label())
            ->assertSeeIn('@pokedex-species-metadata', SpeciesTrait::BABY->label())
            ->assertSeeIn('@pokedex-species-metadata', SpeciesTrait::LEGENDARY->label())
            ->assertSeeIn('@pokedex-species-metadata', SpeciesTrait::MYTHICAL->label())
            ->assertSeeIn('@pokedex-species-metadata', SpeciesTrait::MEGA->label())
            ->assertSeeIn('@pokedex-species-metadata', SpeciesTrait::GMAX->label());
    });

    it('does not displays missing traits species traits', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create([
            'is_baby'      => false,
            'is_legendary' => true,
            'is_mythical'  => false,
            'is_mega'      => false,
            'is_gmax'      => false,
        ]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-metadata', SpeciesTrait::LEGENDARY->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::BABY->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::MYTHICAL->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::MEGA->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::GMAX->label());
    });

    it('displays an empty state message if species has no traits', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create([
            'is_baby'      => false,
            'is_legendary' => false,
            'is_mythical'  => false,
            'is_mega'      => false,
            'is_gmax'      => false,
        ]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-metadata', 'None')
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::BABY->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::LEGENDARY->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::MYTHICAL->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::MEGA->label())
            ->assertDontSeeIn('@pokedex-species-metadata', SpeciesTrait::GMAX->label());
    });
});

it('displays the evolution chain card', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->assertSee('Evolutions');
});

it('displays the damage taken card', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->assertPresent('@pokedex-species-damage-taken');
});

it('displays the capture statistics card', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->assertPresent('@pokedex-species-capture-statistics');
});

describe('autocomplete search', function () {
    it('searches species when typing in the search bar', function () {
        $user = User::factory()->create();

        Species::factory()->create(['name' => 'Pichu']);
        Species::factory()->create(['name' => 'Pikachu']);
        $raichu = Species::factory()->create(['name' => 'Raichu']);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $raichu))
            ->type('search', 'Pi')
            ->wait(1)
            ->assertCount('[role="option"]', 2)
            ->assertSee('Pikachu')
            ->assertSee('Pichu');
    });

    it('navigates to the selected species from autocomplete', function () {
        $user = User::factory()->create();

        $pichu   = Species::factory()->create(['name' => 'Pichu']);
        $pikachu = Species::factory()->create(['name' => 'Pikachu']);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $pichu))
            ->type('search', 'Pik')
            ->wait(1)
            ->assertSee('Pikachu')
            ->click('Pikachu')
            ->assertPathIs(route('dex.web.pokedex.show', $pikachu, absolute: false))
            ->assertNoSmoke();
    });

    it('shows no results message for non-existent species', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create(['name' => 'Pikachu']);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->type('search', 'non-existent')
            ->wait(1)
            ->assertPresent('@pokedex-species-autocomplete-empty-state');
    });
});
