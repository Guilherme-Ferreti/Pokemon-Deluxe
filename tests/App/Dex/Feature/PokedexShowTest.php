<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
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
            ->component('Dex/pages/Pokedex/Show/index')
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
            ->has('overview')
            ->where('overview.types', Arr::pluck($species->types, 'value'))
            ->where('overview.portraits.regular', $species->portraits->regular)
            ->where('overview.portraits.shiny', $species->portraits->shiny)
            ->where('overview.traits.isBaby', $species->is_baby)
            ->where('overview.traits.isLegendary', $species->is_legendary)
            ->where('overview.traits.isMythical', $species->is_mythical)
            ->where('overview.traits.isMega', $species->is_mega)
            ->where('overview.traits.isGmax', $species->is_gmax)
            ->where('overview.traits.isUltraBeast', $species->is_ultra_beast)
            ->where('overview.generation', $species->generation)
            ->where('overview.catchRatePercentage', $species->getCatchRatePercentage())
            ->where('overview.isGenderless', $species->isGenderless())
            ->where('overview.malePercentage', $species->gender_rate->malePercentage())
            ->where('overview.femalePercentage', $species->gender_rate->femalePercentage())
            ->has('damageTaken', count(PokemonType::cases()))
            ->has('catchStatistics.global.regular')
            ->has('catchStatistics.global.shiny')
            ->has('catchStatistics.currentUser.regular')
            ->has('catchStatistics.currentUser.shiny')
            ->where('evolutionChain.previous', [])
            ->where('evolutionChain.into', [])
            ->where('baseMoves', [])
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

it("reflects the current user's newly caught pokemon in the catch statistics", function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();
    Pokemon::factory()->for($user)->for($species)->regular()->create();

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('catchStatistics.currentUser.regular', 1)
            ->where('catchStatistics.currentUser.shiny', 0)
        );

    Pokemon::factory()->for($user)->for($species)->shiny()->create();

    get(route('dex.web.pokedex.show', $species))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('catchStatistics.currentUser.regular', 1)
            ->where('catchStatistics.currentUser.shiny', 1)
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

it('displays the species base moves ordered by name', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    $tackle    = Move::factory()->create(['name' => 'Tackle']);
    $hyperBeam = Move::factory()->create(['name' => 'Hyper Beam']);

    $species->baseMoves()->attach([$tackle->id, $hyperBeam->id]);

    actingAs($user);

    get(route('dex.web.pokedex.show', $species))
        ->assertInertia(fn (Assert $page) => $page
            ->has('baseMoves', 2)
            ->where('baseMoves.0.name', $hyperBeam->name)
            ->where('baseMoves.0.type', $hyperBeam->type->value)
            ->where('baseMoves.0.category', $hyperBeam->category->value)
            ->where('baseMoves.0.power', $hyperBeam->power)
            ->where('baseMoves.0.accuracy', $hyperBeam->accuracy)
            ->where('baseMoves.0.ailment', $hyperBeam->ailment?->value)
            ->where('baseMoves.0.ailmentChance', $hyperBeam->ailment_chance)
            ->where('baseMoves.0.flinchChance', $hyperBeam->flinch_chance)
            ->where('baseMoves.0.isZMove', $hyperBeam->is_z_move)
            ->where('baseMoves.0.isDynamaxMove', $hyperBeam->is_dynamax_move)
            ->where('baseMoves.1.name', $tackle->name)
        );
});
