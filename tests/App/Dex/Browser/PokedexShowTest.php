<?php

declare(strict_types=1);

use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonGender;
use Shared\Enums\SpeciesTrait;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\SpeciesLearnableMove;
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
        ->click('@pokedex-species-navigation__previous-link')
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
        ->click('@pokedex-species-navigation__next-link')
        ->assertPathIs(route('dex.web.pokedex.show', $nextSpecies, absolute: false))
        ->assertSee($nextSpecies->name)
        ->assertNoSmoke();
});

it('displays the species hero card', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    actingAs($user);

    visit(route('dex.web.pokedex.show', $species))
        ->assertSeeIn('@pokedex-species-hero__name', $species->name)
        ->assertSeeIn('@pokedex-species-hero__pokedex-number', $species->pokedex_number)
        ->assertSeeIn('@pokedex-species-hero__genus', $species->genus)
        ->assertSeeIn('@pokedex-species-hero__text-entry', $species->text_entry);
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
            'gender_rate'  => 2,
        ]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-metadata__types', $species->types[0]->label())
            ->assertSeeIn('@pokedex-species-metadata__types', ($species->types[1] ?? null)?->label())
            ->assertSeeIn('@pokedex-species-metadata__generation', $species->generation->label())
            ->assertSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::BABY->label())
            ->assertSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::LEGENDARY->label())
            ->assertSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::MYTHICAL->label())
            ->assertSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::MEGA->label())
            ->assertSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::GMAX->label())
            ->assertSeeIn('@pokedex-species-metadata__gender-rate', $species->getMalePercentage())
            ->assertSeeIn('@pokedex-species-metadata__gender-rate', $species->getFemalePercentage());
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
            ->assertSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::LEGENDARY->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::BABY->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::MYTHICAL->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::MEGA->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::GMAX->label());
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
            ->assertSeeIn('@pokedex-species-metadata__traits', 'None')
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::BABY->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::LEGENDARY->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::MYTHICAL->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::MEGA->label())
            ->assertDontSeeIn('@pokedex-species-metadata__traits', SpeciesTrait::GMAX->label());
    });

    it('displays an empty state message if species is genderless', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create(['gender_rate' => -1]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-metadata__gender-rate', PokemonGender::GENDERLESS->label());
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
            ->assertPresent('@pokedex-species-autocomplete__empty-state');
    });
});

describe('learnable moves card', function () {
    it('displays the card with both move groups', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertPresent('@pokedex-species-learnable-moves')
            ->assertSeeIn('@pokedex-species-learnable-moves', 'Moves Learned')
            ->assertSeeIn('@pokedex-species-learnable-moves', 'By level up')
            ->assertSeeIn('@pokedex-species-learnable-moves', 'By TM')
            ->assertNoSmoke();
    });

    it('displays the moves learned by level up', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $move = Move::factory()->create(['category' => MoveCategory::DAMAGE]);

        $learnableMove = SpeciesLearnableMove::factory()->for($species)->for($move, 'move')->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-learnable-moves__level-up', $learnableMove->level_learn)
            ->assertSeeIn('@pokedex-species-learnable-moves__level-up', $move->name)
            ->assertSeeIn('@pokedex-species-learnable-moves__level-up', $move->type->label())
            ->assertSeeIn('@pokedex-species-learnable-moves__level-up', $move->power)
            ->assertSeeIn('@pokedex-species-learnable-moves__level-up', "{$move->accuracy}%")
            ->assertNoSmoke();
    });

    it('displays the moves learned by TM', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $move = Move::factory()->create(['category' => MoveCategory::DAMAGE]);

        SpeciesLearnableMove::factory()->for($species)->for($move, 'move')->learnedByTM()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@pokedex-species-learnable-moves__tm', $move->tm_code)
            ->assertSeeIn('@pokedex-species-learnable-moves__tm', $move->name)
            ->assertSeeIn('@pokedex-species-learnable-moves__tm', $move->type->label())
            ->assertSeeIn('@pokedex-species-learnable-moves__tm', $move->power)
            ->assertSeeIn('@pokedex-species-learnable-moves__tm', "{$move->accuracy}%")
            ->assertNoSmoke();
    });

    it('sorts the moves learned by level up by level', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $lowLevelMove  = Move::factory()->create(['name' => 'Tackle']);
        $highLevelMove = Move::factory()->create(['name' => 'Hyper Beam']);

        SpeciesLearnableMove::factory()->for($species)->for($lowLevelMove, 'move')->create(['level_learn' => 1]);
        SpeciesLearnableMove::factory()->for($species)->for($highLevelMove, 'move')->create(['level_learn' => 50]);

        actingAs($user);

        $levelCell  = '[data-test="pokedex-species-learnable-moves__level-up"] tbody tr:first-child td:first-child';
        $sortButton = '[data-test="pokedex-species-learnable-moves__level-up"] thead [aria-label="Sort by level"]';

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn($levelCell, '1')
            ->click($sortButton)
            ->wait(0.5)
            ->assertSeeIn($levelCell, '50')
            ->click($sortButton)
            ->wait(0.5)
            ->assertSeeIn($levelCell, '1')
            ->assertNoSmoke();
    });

    it('sorts the moves learned by TM by move', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $firstMove  = Move::factory()->create(['name' => 'Ember']);
        $secondMove = Move::factory()->create(['name' => 'Tackle']);

        SpeciesLearnableMove::factory()->for($species)->for($firstMove, 'move')->learnedByTM()->create();
        SpeciesLearnableMove::factory()->for($species)->for($secondMove, 'move')->learnedByTM()->create();

        actingAs($user);

        $moveCell   = '[data-test="pokedex-species-learnable-moves__tm"] tbody tr:first-child td:nth-child(2)';
        $sortButton = '[data-test="pokedex-species-learnable-moves__tm"] thead [aria-label="Sort by move"]';

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn($moveCell, 'Ember')
            ->click($sortButton)
            ->wait(0.5)
            ->assertSeeIn($moveCell, 'Ember')
            ->click($sortButton)
            ->wait(0.5)
            ->assertSeeIn($moveCell, 'Tackle')
            ->assertNoSmoke();
    });
});
