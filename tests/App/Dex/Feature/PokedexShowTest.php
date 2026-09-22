<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\ElementalType;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
use Shared\Models\SpeciesLearnableMove;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    $species = Species::factory()->create();

    get(route('dex.web.pokedex.show', $species))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the species show page with expected structure', function () {
    $user            = User::factory()->create();
    $species         = Species::factory()->create(['pokedex_number' => 2]);
    $previousSpecies = Species::factory()->create(['pokedex_number' => 1]);
    $nextSpecies     = Species::factory()->create(['pokedex_number' => 3]);

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dex/pages/Pokedex/Show')
            ->has('species')
            ->where('species.name', $species->name)
            ->where('species.slug', $species->slug)
            ->has('hero')
            ->where('hero.pokedexNumber', $species->pokedex_number)
            ->where('hero.genus', $species->genus)
            ->where('hero.textEntry', $species->text_entry)
            ->where('hero.sprites.frontDefault', $species->sprites->front_default)
            ->where('hero.sprites.frontFemale', $species->sprites->front_female)
            ->where('hero.sprites.frontShiny', $species->sprites->front_shiny)
            ->where('hero.sprites.frontShinyFemale', $species->sprites->front_shiny_female)
            ->has('metadata')
            ->where('metadata.types', Arr::pluck($species->types, 'value'))
            ->where('metadata.portraits.regular', $species->portraits->regular)
            ->where('metadata.portraits.shiny', $species->portraits->shiny)
            ->where('metadata.traits.isBaby', $species->is_baby)
            ->where('metadata.traits.isLegendary', $species->is_legendary)
            ->where('metadata.traits.isMythical', $species->is_mythical)
            ->where('metadata.traits.isMega', $species->is_mega)
            ->where('metadata.traits.isGmax', $species->is_gmax)
            ->where('metadata.generation', $species->generation->label())
            ->where('metadata.captureRatePercentage', $species->getCaptureRatePercentage())
            ->where('metadata.isGenderless', $species->isGenderless())
            ->where('metadata.malePercentage', $species->getMalePercentage())
            ->where('metadata.femalePercentage', $species->getFemalePercentage())
            ->has('damageTaken', count(ElementalType::cases()))
            ->has('captureStatistics.global.regular')
            ->has('captureStatistics.global.shiny')
            ->has('captureStatistics.currentUser.regular')
            ->has('captureStatistics.currentUser.shiny')
            ->where('evolutionChain.previous', [])
            ->where('evolutionChain.into', [])
            ->where('learnableMoves.levelUp', [])
            ->where('learnableMoves.tm', [])
            ->has('previousSpecies')
            ->where('previousSpecies.name', $previousSpecies->name)
            ->where('previousSpecies.pokedexNumber', $previousSpecies->pokedex_number)
            ->where('previousSpecies.portrait', $previousSpecies->portraits->regular)
            ->where('previousSpecies.slug', $previousSpecies->slug)
            ->has('nextSpecies')
            ->where('nextSpecies.name', $nextSpecies->name)
            ->where('nextSpecies.pokedexNumber', $nextSpecies->pokedex_number)
            ->where('nextSpecies.portrait', $nextSpecies->portraits->regular)
            ->where('nextSpecies.slug', $nextSpecies->slug)
        );
});

it('returns null for previous species when at first entry', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create(['pokedex_number' => 1]);

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertInertia(fn (Assert $page) => $page
            ->where('previousSpecies', null)
        );
});

it('returns null for next species when at last entry', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create(['pokedex_number' => 1000]);

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextSpecies', null)
        );
});

it('displays evolution chain', function () {
    $user = User::factory()->create();

    $basic         = Species::factory()->create();
    $stageOne      = Species::factory()->create();
    $stageTwo      = Species::factory()->create();
    $evolutionItem = EvolutionItem::factory()->create();

    SpeciesEvolution::factory()->for($basic, 'from')->for($stageOne, 'into')->create([
        'evolution_item_id' => null,
        'min_level'         => 16,
        'gender'            => PokemonGender::FEMALE,
    ]);

    SpeciesEvolution::factory()->for($stageOne, 'from')->for($stageTwo, 'into')->for($evolutionItem)->create([
        'min_level' => null,
        'gender'    => null,
    ]);

    actingAs($user);

    get(route('dex.web.pokedex.show', $stageOne))
        ->assertInertia(fn (Assert $page) => $page
            ->has('evolutionChain.previous', 1)
            ->where('evolutionChain.previous.0.species.name', $basic->name)
            ->where('evolutionChain.previous.0.species.slug', $basic->slug)
            ->where('evolutionChain.previous.0.species.portrait', $basic->portraits->regular)
            ->where('evolutionChain.previous.0.minLevel', 16)
            ->where('evolutionChain.previous.0.gender', PokemonGender::FEMALE->value)
            ->where('evolutionChain.previous.0.evolutionItem', null)
            ->has('evolutionChain.into', 1)
            ->where('evolutionChain.into.0.species.name', $stageTwo->name)
            ->where('evolutionChain.into.0.species.slug', $stageTwo->slug)
            ->where('evolutionChain.into.0.species.portrait', $stageTwo->portraits->regular)
            ->where('evolutionChain.into.0.minLevel', null)
            ->where('evolutionChain.into.0.gender', null)
            ->where('evolutionChain.into.0.evolutionItem.name', $evolutionItem->name)
            ->where('evolutionChain.into.0.evolutionItem.sprite', $evolutionItem->sprite)
        );
});

it('displays learnable moves split into level up and tm', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    $levelUpMove = Move::factory()->create();
    $tmMove      = Move::factory()->create();

    SpeciesLearnableMove::factory()->for($species)->for($levelUpMove, 'move')->create(['level_learn' => 5]);
    SpeciesLearnableMove::factory()->for($species)->for($tmMove, 'move')->learnedByTM()->create();

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertInertia(fn (Assert $page) => $page
            ->has('learnableMoves.levelUp', 1)
            ->where('learnableMoves.levelUp.0.name', $levelUpMove->name)
            ->where('learnableMoves.levelUp.0.type', $levelUpMove->type->value)
            ->where('learnableMoves.levelUp.0.category', $levelUpMove->category->value)
            ->where('learnableMoves.levelUp.0.power', $levelUpMove->power)
            ->where('learnableMoves.levelUp.0.accuracy', $levelUpMove->accuracy)
            ->where('learnableMoves.levelUp.0.isZMove', $levelUpMove->is_z_move)
            ->where('learnableMoves.levelUp.0.isDynamaxMove', $levelUpMove->is_dynamax_move)
            ->where('learnableMoves.levelUp.0.levelLearn', 5)
            ->has('learnableMoves.tm', 1)
            ->where('learnableMoves.tm.0.name', $tmMove->name)
            ->where('learnableMoves.tm.0.type', $tmMove->type->value)
            ->where('learnableMoves.tm.0.category', $tmMove->category->value)
            ->where('learnableMoves.tm.0.power', $tmMove->power)
            ->where('learnableMoves.tm.0.accuracy', $tmMove->accuracy)
            ->where('learnableMoves.tm.0.isZMove', $tmMove->is_z_move)
            ->where('learnableMoves.tm.0.isDynamaxMove', $tmMove->is_dynamax_move)
            ->where('learnableMoves.tm.0.tmCode', $tmMove->tm_code)
        );
});

it('orders learnable moves by level then by name', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    $highLevelMove = Move::factory()->create(['name' => 'Hyper Beam']);
    $lowLevelMove  = Move::factory()->create(['name' => 'Tackle']);

    SpeciesLearnableMove::factory()->for($species)->for($highLevelMove, 'move')->create(['level_learn' => 50]);
    SpeciesLearnableMove::factory()->for($species)->for($lowLevelMove, 'move')->create(['level_learn' => 1]);

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertInertia(fn (Assert $page) => $page
            ->has('learnableMoves.levelUp', 2)
            ->where('learnableMoves.levelUp.0.name', $lowLevelMove->name)
            ->where('learnableMoves.levelUp.0.levelLearn', 1)
            ->where('learnableMoves.levelUp.1.name', $highLevelMove->name)
            ->where('learnableMoves.levelUp.1.levelLearn', 50)
        );
});
