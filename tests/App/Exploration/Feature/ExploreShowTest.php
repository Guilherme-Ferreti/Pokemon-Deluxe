<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\Area;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Encounter;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('exploration.web.show', Area::FOREST))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the area page with expected structure', function () {
    $user    = User::factory()->create();
    $scyther = Species::factory()->create([
        'name'         => 'Scyther',
        'types'        => [PokemonType::BUG, PokemonType::FLYING],
        'is_legendary' => false,
        'is_mythical'  => false,
    ]);
    Encounter::factory()->for($user)->for($scyther)->create([
        'area'     => Area::FOREST,
        'level'    => 9,
        'is_shiny' => true,
        'gender'   => PokemonGender::MALE,
    ]);

    actingAs($user);

    get(route('exploration.web.show', Area::FOREST))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Exploration/pages/Explore/Show/index')
            ->where('area', [
                'slug'  => 'forest',
                'label' => 'Forest',
                'types' => [PokemonType::GRASS->value, PokemonType::BUG->value],
            ])
            ->where('encounter', [
                'sprite'      => $scyther->getDisplayFrontSprite(PokemonGender::MALE, shiny: true),
                'name'        => 'Shiny Scyther',
                'gender'      => PokemonGender::MALE->value,
                'level'       => 9,
                'types'       => [PokemonType::BUG->value, PokemonType::FLYING->value],
                'isShiny'     => true,
                'isLegendary' => false,
                'isMythical'  => false,
            ])
            ->where('isBattleInProgress', false)
        );
});

it('flags a legendary or mythical encounter', function (string $trait) {
    $user    = User::factory()->create();
    $species = Species::factory()->create(['is_legendary' => false, 'is_mythical' => false, $trait => true]);
    Encounter::factory()->for($user)->for($species)->create(['area' => Area::FOREST, 'is_shiny' => false]);

    actingAs($user);

    get(route('exploration.web.show', Area::FOREST))
        ->assertInertia(fn (Assert $page) => $page
            ->where('encounter.isShiny', false)
            ->where('encounter.isLegendary', $trait === 'is_legendary')
            ->where('encounter.isMythical', $trait === 'is_mythical')
        );
})->with(['is_legendary', 'is_mythical']);

it('shows no encounter when the player has none in this area', function () {
    $user = User::factory()->create();
    Encounter::factory()->for($user)->create(['area' => Area::COAST]);
    Encounter::factory()->create(['area' => Area::FOREST]);

    actingAs($user);

    get(route('exploration.web.show', Area::FOREST))
        ->assertInertia(fn (Assert $page) => $page->where('encounter', null));
});

it('tells the player a battle of theirs is in progress', function () {
    $user = User::factory()->create();
    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);
    Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    actingAs($user);

    get(route('exploration.web.show', Area::FOREST))
        ->assertInertia(fn (Assert $page) => $page->where('isBattleInProgress', true));
});

it('loads the area species only when requested', function () {
    $user     = User::factory()->create();
    $caterpie = Species::factory()->create([
        'name'           => 'Caterpie',
        'types'          => [PokemonType::BUG],
        'pokedex_order'  => 10,
        'wild_min_level' => 2,
        'wild_max_level' => 12,
    ]);
    $bulbasaur = Species::factory()->create([
        'name'           => 'Bulbasaur',
        'types'          => [PokemonType::GRASS, PokemonType::POISON],
        'pokedex_order'  => 1,
        'wild_min_level' => 2,
        'wild_max_level' => 12,
    ]);
    Species::factory()->create(['types' => [PokemonType::FIRE], 'wild_min_level' => 2, 'wild_max_level' => 12]);
    Species::factory()->create(['types' => [PokemonType::BUG], 'wild_min_level' => null, 'wild_max_level' => null]);

    actingAs($user);

    get(route('exploration.web.show', Area::FOREST))
        ->assertInertia(fn (Assert $page) => $page
            ->missing('areaSpecies')
            ->reloadOnly('areaSpecies', fn (Assert $reload) => $reload
                ->where('areaSpecies', [
                    [
                        'slug'     => $bulbasaur->slug,
                        'name'     => 'Bulbasaur',
                        'portrait' => $bulbasaur->portraits->regular,
                        'types'    => [PokemonType::GRASS->value, PokemonType::POISON->value],
                    ],
                    [
                        'slug'     => $caterpie->slug,
                        'name'     => 'Caterpie',
                        'portrait' => $caterpie->portraits->regular,
                        'types'    => [PokemonType::BUG->value],
                    ],
                ])
            )
        );
});

it('returns not found for an unknown area', function () {
    actingAs(User::factory()->create());

    get('/explore/moon')->assertNotFound();
});
