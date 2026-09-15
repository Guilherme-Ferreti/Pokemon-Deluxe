<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\ElementalType;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
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
            ->component('Dex/Pokedex/Show')
            ->has('species')
            ->where('species.id', $species->id)
            ->where('species.name', $species->name)
            ->where('species.pokedexNumber', $species->pokedex_number)
            ->where('species.genus', $species->genus)
            ->where('species.textEntry', $species->text_entry)
            ->where('species.types', $species->types->pluck('value'))
            ->where('species.generation', $species->generation->label())
            ->where('species.slug', $species->slug)
            ->where('species.isGenderless', $species->isGenderless())
            ->where('species.malePercentage', $species->getMalePercentage())
            ->where('species.femalePercentage', $species->getFemalePercentage())
            ->has('damageTaken', count(ElementalType::cases()))
            ->has('captureStatistics.global.regular')
            ->has('captureStatistics.global.shiny')
            ->has('captureStatistics.currentUser.regular')
            ->has('captureStatistics.currentUser.shiny')
            ->where('evolutionChain.previous', [])
            ->where('evolutionChain.into', [])
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
