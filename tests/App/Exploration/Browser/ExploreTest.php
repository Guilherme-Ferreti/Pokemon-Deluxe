<?php

declare(strict_types=1);

use Shared\Enums\Area;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\actingAs;

it('explores an area, keeps exploring and starts a battle', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();
    Species::factory()->withBaseMoves()->create([
        'name'            => 'Caterpie',
        'types'           => [PokemonType::BUG],
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::EQUAL,
        'wild_min_level'  => 2,
        'wild_max_level'  => 12,
    ]);

    actingAs($user);

    visit(route('exploration.web.index'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->click('Forest')
        ->assertPathIs('/explore/forest')
        ->assertSeeIn('@encounter-empty-state', 'Nothing here yet')
        ->click('@encounter-empty-state__explore-button')
        ->assertSeeIn('@encounter-card__title', 'Caterpie appeared!')
        ->assertNoAccessibilityIssues()
        ->click('@encounter-card__keep-exploring-button')
        ->assertSeeIn('@encounter-card__title', 'Caterpie appeared!')
        ->click('@encounter-card__battle-button')
        ->assertPathIs('/battle')
        ->assertSeeIn('@battle-header__title', 'Wild battle');
});

it('shows a battle in progress instead of the encounter', function () {
    $user = User::factory()->create();
    Battle::factory()->for($user)->create();

    actingAs($user);

    visit(route('exploration.web.show', Area::FOREST))
        ->assertNoSmoke()
        ->assertSeeIn('@battle-in-progress-empty-state', "You're in a battle")
        ->assertMissing('@encounter-empty-state');
});

it('keeps exploring the battle area from the result card', function () {
    $user     = User::factory()->create();
    $caterpie = Species::factory()->create([
        'name'            => 'Caterpie',
        'types'           => [PokemonType::BUG],
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::EQUAL,
        'wild_min_level'  => 2,
        'wild_max_level'  => 12,
    ]);
    $tackle = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $caterpie->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::BUG],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::FORFEITED,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
            area: Area::FOREST,
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertSeeIn('@battle-result-card', 'Back to Dashboard')
        ->click('@battle-result-card__keep-exploring-button')
        ->assertPathIs('/explore/forest')
        ->assertSeeIn('@encounter-card__title', 'Caterpie appeared!');
});

it('lists the area species with search and type filters', function () {
    Species::factory()->create(['name' => 'Caterpie', 'types' => [PokemonType::BUG], 'wild_min_level' => 2, 'wild_max_level' => 12]);
    Species::factory()->create(['name' => 'Bulbasaur', 'types' => [PokemonType::GRASS, PokemonType::POISON], 'wild_min_level' => 2, 'wild_max_level' => 12]);
    Species::factory()->create(['name' => 'Charmander', 'types' => [PokemonType::FIRE], 'wild_min_level' => 2, 'wild_max_level' => 12]);

    actingAs(User::factory()->create());

    visit(route('exploration.web.show', Area::FOREST))
        ->click('@area-species-dialog__open-button')
        ->assertSeeIn('@area-species-dialog', '2 species')
        ->assertSeeIn('@area-species-dialog__species-list', 'Caterpie')
        ->assertSeeIn('@area-species-dialog__species-list', 'Bulbasaur')
        ->assertDontSeeIn('@area-species-dialog__species-list', 'Charmander')
        ->assertNoAccessibilityIssues()
        ->click('@area-species-dialog__type-poison-button')
        ->assertSeeIn('@area-species-dialog__species-list', 'Bulbasaur')
        ->assertDontSeeIn('@area-species-dialog__species-list', 'Caterpie')
        ->fill('@area-species-dialog__search-input', 'Cater')
        ->assertMissing('@area-species-dialog__species-list')
        ->assertSeeIn('@area-species-dialog__no-match-empty-state', 'No Pokémon match your search')
        ->click('@area-species-dialog__type-poison-button')
        ->assertSeeIn('@area-species-dialog__species-list', 'Caterpie')
        ->assertDontSeeIn('@area-species-dialog__species-list', 'Bulbasaur');
});
