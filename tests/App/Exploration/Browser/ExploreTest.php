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
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $caterpie->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::BUG],
        'move_ids'                      => [$tackle->id],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::FORFEITED,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
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
