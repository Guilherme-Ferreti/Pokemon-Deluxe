<?php

declare(strict_types=1);

use Shared\Enums\Ailment;
use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\SpeciesTrait;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
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
            ->assertSeeIn('@overview-card__gender-rate-male', $species->gender_rate->malePercentage())
            ->assertSeeIn('@overview-card__gender-rate-female', $species->gender_rate->femalePercentage());
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
            ->assertVisible('@overview-card__gender-rate-genderless');
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

describe('base moves card', function () {
    it('displays the card', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertVisible('@base-moves-card')
            ->assertSeeIn('@base-moves-card', 'Base Moves')
            ->assertNoSmoke();
    });

    it('displays the species base moves', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $move = Move::factory()->create([
            'category'       => MoveCategory::DAMAGE,
            'ailment'        => Ailment::BURN,
            'ailment_chance' => 10,
            'flinch_chance'  => 30,
        ]);

        $species->baseMoves()->attach($move);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->assertSeeIn('@base-moves-card__row-1-name', $move->name)
            ->assertSeeIn('@base-moves-card__row-1-type', $move->type->label())
            ->assertSeeIn('@base-moves-card__row-1-power', $move->power)
            ->assertSeeIn('@base-moves-card__row-1-accuracy', "{$move->accuracy}%")
            ->assertSeeIn('@base-moves-card__row-1-ailment', Ailment::BURN->label())
            ->assertSeeIn('@base-moves-card__row-1-ailment-chance', '10%')
            ->assertSeeIn('@base-moves-card__row-1-flinch-chance', '30%')
            ->assertNoSmoke();
    });

    it('sorts powerless moves first when sorting by power in ascending order', function () {
        $user    = User::factory()->create();
        $species = Species::factory()->create();

        $powerlessMove = Move::factory()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT]);
        $powerfulMove  = Move::factory()->create(['name' => 'Hyper Beam', 'category' => MoveCategory::DAMAGE, 'power' => 150]);

        $species->baseMoves()->attach([$powerlessMove->id, $powerfulMove->id]);

        actingAs($user);

        visit(route('dex.web.pokedex.show', $species))
            ->click('@base-moves-card__head-power')
            ->wait(0.5)
            ->assertSeeIn('@base-moves-card__row-1-name', 'Growl')
            ->click('@base-moves-card__head-power')
            ->wait(0.5)
            ->assertSeeIn('@base-moves-card__row-1-name', 'Hyper Beam')
            ->assertNoSmoke();
    });
});
