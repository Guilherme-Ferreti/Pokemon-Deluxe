<?php

declare(strict_types=1);

use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\SpeciesTrait;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
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

describe('species search input', function () {
    it('searches species when typing in the search input', function () {
        $user = User::factory()->create();

        Species::factory()->create(['name' => 'Pichu']);
        Species::factory()->create(['name' => 'Pikachu']);
        $raichu = Species::factory()->create(['name' => 'Raichu']);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $raichu))
            ->type('search', 'Pi')
            ->wait(1)
            ->assertCount('[role="option"]', 2)
            ->assertSeeIn('@species-search-input__autocomplete-options-list', 'Pikachu')
            ->assertSeeIn('@species-search-input__autocomplete-options-list', 'Pichu');
    });

    it('navigates to the selected species from autocomplete', function () {
        $user = User::factory()->create();

        $pichu   = Species::factory()->create(['name' => 'Pichu']);
        $pikachu = Species::factory()->create(['name' => 'Pikachu']);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $pichu))
            ->type('search', 'Pik')
            ->wait(1)
            ->assertSeeIn('@species-search-input__autocomplete-options-list', 'Pikachu')
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
            ->assertVisible('@species-search-input__empty-state');
    });
});

describe('navigation links', function () {
    it('navigates to previous species', function () {
        $user = User::factory()->create();

        $previousSpecies = Species::factory()->create(['pokedex_number' => 2]);
        $species         = Species::factory()->create(['pokedex_number' => 3]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->click('@navigation-links__previous-link')
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
            ->click('@navigation-links__next-link')
            ->assertPathIs(route('dex.web.pokedex.show', $nextSpecies, absolute: false))
            ->assertSee($nextSpecies->name)
            ->assertNoSmoke();
    });
});

describe('species hero card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@species-hero__name', $species->name)
            ->assertSeeIn('@species-hero__pokedex-number', $species->pokedex_number)
            ->assertSeeIn('@species-hero__genus', $species->genus)
            ->assertSeeIn('@species-hero__text-entry', $species->text_entry);
    });
});

describe('overview card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->dualType()->create([
            'is_baby'      => true,
            'is_legendary' => true,
            'is_mythical'  => true,
            'is_mega'      => true,
            'is_gmax'      => true,
            'gender_rate'  => PokemonGenderRate::EQUAL,
        ]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@overview-card__types', $species->types[0]->label())
            ->assertSeeIn('@overview-card__types', ($species->types[1] ?? null)?->label())
            ->assertSeeIn('@overview-card__generation', $species->generation->label())
            ->assertSeeIn('@overview-card__traits', SpeciesTrait::BABY->label())
            ->assertSeeIn('@overview-card__traits', SpeciesTrait::LEGENDARY->label())
            ->assertSeeIn('@overview-card__traits', SpeciesTrait::MYTHICAL->label())
            ->assertSeeIn('@overview-card__traits', SpeciesTrait::MEGA->label())
            ->assertSeeIn('@overview-card__traits', SpeciesTrait::GMAX->label())
            ->assertSeeIn('@overview-card__gender-rate', $species->gender_rate->malePercentage())
            ->assertSeeIn('@overview-card__gender-rate', $species->gender_rate->femalePercentage());
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
            ->assertVisible('@overview-card')
            ->assertSeeIn('@overview-card__traits', SpeciesTrait::LEGENDARY->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::BABY->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::MYTHICAL->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::MEGA->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::GMAX->label());
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
            ->assertSeeIn('@overview-card__traits', 'None')
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::BABY->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::LEGENDARY->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::MYTHICAL->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::MEGA->label())
            ->assertDontSeeIn('@overview-card__traits', SpeciesTrait::GMAX->label());
    });

    it('displays the genderless label if species is genderless', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@overview-card__gender-rate', PokemonGender::GENDERLESS->label());
    });
});

describe('evolution chain card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $evolutionItem = EvolutionItem::factory()->create();

        $previousEvolution = SpeciesEvolution::factory()->for($species, 'into')->for($evolutionItem)->create();
        $nextEvolution     = SpeciesEvolution::factory()->for($species, 'from')->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertVisible('@evolution-chain-card')
            ->assertSeeIn('@evolution-chain-card__evolution-1-species-link', $previousEvolution->from->name)
            ->assertSeeIn('@evolution-chain-card__evolution-1-item', $evolutionItem->name)
            ->assertSeeIn('@evolution-chain-card__evolution-2-species-link', $nextEvolution->into->name);
    });
});

describe('damage taken card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertVisible('@damage-taken-card');
    });
});

describe('catch statistics card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertVisible('@catch-stats-statistics-card');
    });
});

describe('learnable moves card', function () {
    it('displays the card with both move groups', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertVisible('@learnable-moves-card')
            ->assertSeeIn('@learnable-moves-card', 'Moves Learned')
            ->assertSeeIn('@learnable-moves-card', 'By level up')
            ->assertVisible('@learnable-moves-level-up-table')
            ->assertSeeIn('@learnable-moves-card', 'By TM')
            ->assertVisible('@learnable-moves-tm-table')
            ->assertNoSmoke();
    });

    it('displays the moves learned by level up', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $move = Move::factory()->create(['category' => MoveCategory::DAMAGE]);

        $learnableMove = SpeciesLearnableMove::factory()->for($species)->for($move)->byLevelUp()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-level-learn', $learnableMove->level_learn)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-name', $move->name)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-type', $move->type->label())
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-power', $move->power)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-accuracy', "{$move->accuracy}%")
            ->assertNoSmoke();
    });

    it('displays the moves learned by TM', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $move = Move::factory()->create(['category' => MoveCategory::DAMAGE]);

        SpeciesLearnableMove::factory()->for($species)->for($move)->byTM()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@learnable-moves-tm-table__row-1-tm-code', $move->tm_code)
            ->assertSeeIn('@learnable-moves-tm-table__row-1-name', $move->name)
            ->assertSeeIn('@learnable-moves-tm-table__row-1-type', $move->type->label())
            ->assertSeeIn('@learnable-moves-tm-table__row-1-power', $move->power)
            ->assertSeeIn('@learnable-moves-tm-table__row-1-accuracy', "{$move->accuracy}%")
            ->assertNoSmoke();
    });

    it('sorts the moves learned by level up by level', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $lowLevelMove  = Move::factory()->create();
        $highLevelMove = Move::factory()->create();

        SpeciesLearnableMove::factory()->for($species)->for($lowLevelMove)->byLevelUp(1)->create();
        SpeciesLearnableMove::factory()->for($species)->for($highLevelMove)->byLevelUp(50)->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-level-learn', '1')
            ->click('@learnable-moves-level-up-table__head-level-learn')
            ->wait(0.5)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-level-learn', '50')
            ->click('@learnable-moves-level-up-table__head-level-learn')
            ->wait(0.5)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-level-learn', '1')
            ->assertNoSmoke();
    });

    it('sorts the moves learned by TM by TM Code', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $firstMove  = Move::factory()->create(['tm_code' => 'TM001']);
        $secondMove = Move::factory()->create(['tm_code' => 'TM002']);

        SpeciesLearnableMove::factory()->for($species)->for($firstMove)->byTM()->create();
        SpeciesLearnableMove::factory()->for($species)->for($secondMove)->byTM()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@learnable-moves-tm-table__row-1-name', $firstMove->name)
            ->click('@learnable-moves-tm-table__head-tm-code')
            ->wait(0.5)
            ->assertSeeIn('@learnable-moves-tm-table__row-1-name', $secondMove->name)
            ->click('@learnable-moves-tm-table__head-tm-code')
            ->wait(0.5)
            ->assertSeeIn('@learnable-moves-tm-table__row-1-name', $firstMove->name)
            ->assertNoSmoke();
    });

    it('sorts OHKO moves first when sorting by power in descending order', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $ohkoMove   = Move::factory()->create(['category' => MoveCategory::OHKO]);
        $strongMove = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'power' => 150]);

        SpeciesLearnableMove::factory()->for($species)->for($ohkoMove)->byLevelUp()->create();
        SpeciesLearnableMove::factory()->for($species)->for($strongMove)->byLevelUp()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->click('@learnable-moves-level-up-table__head-power')
            ->wait(0.5)
            ->assertDontSeeIn('@learnable-moves-level-up-table__row-1-name', $ohkoMove->name)
            ->click('@learnable-moves-level-up-table__head-power')
            ->wait(0.5)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-name', $ohkoMove->name)
            ->assertNoSmoke();
    });

    it('sorts powerless moves first when sorting by power in ascending order', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $powerlessMove = Move::factory()->create(['category' => MoveCategory::AILMENT]);
        $powerfullMove = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'power' => 150]);

        SpeciesLearnableMove::factory()->for($species)->for($powerlessMove)->byLevelUp()->create();
        SpeciesLearnableMove::factory()->for($species)->for($powerfullMove)->byLevelUp()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->click('@learnable-moves-level-up-table__head-power')
            ->wait(0.5)
            ->assertSeeIn('@learnable-moves-level-up-table__row-1-name', $powerlessMove->name)
            ->click('@learnable-moves-level-up-table__head-power')
            ->wait(0.5)
            ->assertDontSeeIn('@learnable-moves-level-up-table__row-1-name', $powerlessMove->name)
            ->assertNoSmoke();
    });
});
