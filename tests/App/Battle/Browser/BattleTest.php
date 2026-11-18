<?php

declare(strict_types=1);

use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\ExperienceGain;

use function Pest\Laravel\actingAs;

it('loads the battle page without errors', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            ],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@battle-header__title', 'Wild battle')
        ->assertSeeIn('@battle-header', 'A wild Pikachu appeared · Turn 1')
        ->assertSeeIn('@battlefield-combatant-card--player', 'HP:')
        ->assertSeeIn('@battlefield-combatant-card--player', 'Lv 50')
        ->assertSeeIn('@battlefield-combatant-card--player__hp-indicator', '218 / 218')
        ->assertSeeIn('@battlefield-combatant-card--opponent', 'HP:')
        ->assertScript('document.querySelector(\'[data-test="battlefield-combatant-card--player__name"] svg\') !== null')
        ->assertPresent('@battle-scene-background--grass-field')
        ->assertScript(<<<'JS'
            (() => {
                const field = document.querySelector('[data-test="battlefield-card"]');
                const scene = document.querySelector('[data-test="battle-scene-background--grass-field"]').getBoundingClientRect();
                const card = document.querySelector('[data-test="battlefield-combatant-card--player"]');
                const cardBox = card.getBoundingClientRect();
                const topElement = document.elementFromPoint(cardBox.left + cardBox.width / 2, cardBox.top + cardBox.height / 2);

                return Math.round(scene.width) === field.clientWidth && Math.round(scene.height) === field.clientHeight && card.contains(topElement);
            })()
            JS)
        ->assertNoJavascriptErrors();
});

it('takes a turn when a move is clicked', function () {
    $user         = User::factory()->create();
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemon_id'                    => null,
        'species_id'                    => $pidgey->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::FEMALE,
        'types'                         => [PokemonType::NORMAL, PokemonType::FLYING],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...[
                ...$combatant,
                'pokemon_id' => 'pokemon-1',
                'species_id' => $pikachu->id,
                'gender'     => PokemonGender::MALE,
                'types'      => [PokemonType::ELECTRIC],
                'move_ids'   => [$tackle->id, $thunderShock->id],
            ])], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            ],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertSeeIn('@battlefield-combatant-card--opponent__hp-indicator', '218 / 218')
        ->assertSeeIn('@moves-card', 'Choose a move')
        ->assertAttribute('@moves-card__move-1-radio', 'data-state', 'checked')
        ->click('@moves-card__move-2-radio')
        ->assertAttribute('@moves-card__move-1-radio', 'data-state', 'unchecked')
        ->assertAttribute('@moves-card__move-2-radio', 'data-state', 'checked')
        ->assertScript("document.querySelectorAll('[data-test=\"moves-card\"] [data-slot=\"radio-group-indicator\"]').length", 1)
        ->click('@moves-card__attack-button')
        ->wait(1)
        ->assertSeeIn('@battlefield-combatant-card--opponent__hp-indicator', '125 / 218')
        ->assertSeeIn('@battlefield-combatant-card--player__hp-indicator', '172 / 218')
        ->assertSeeIn('@battle-log-card', 'Pikachu used Thunder Shock!')
        ->assertSeeIn('@battle-log-card', 'The wild Pidgey used Tackle!')
        ->assertScript("document.querySelector('[data-test=\"battle-log-card\"] [data-slot=\"collapsible\"]').dataset.test", 'battle-log-card__turn-1-section')
        ->assertScript("document.querySelector('[data-test=\"battle-log-card__turn-1-line-1\"] strong').textContent", 'Thunder Shock')
        ->assertMissing('@battle-log-card__turn-0-line-1')
        ->click('@battle-log-card__turn-0-button')
        ->assertSeeIn('@battle-log-card__turn-0-line-1', 'A wild Pidgey appeared!')
        ->click('@battle-log-card__turn-1-button')
        ->assertMissing('@battle-log-card__turn-1-line-1')
        ->click('@battle-log-card__turn-0-button')
        ->assertMissing('@battle-log-card__turn-0-line-1')
        ->assertNoJavascriptErrors();
});

it('sends out another pokemon from the team rail after a faint', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'current_hp' => 0]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: true,
            log: [new BattleLogEntry(3, BattleLogKind::EVENT, 'Pikachu fainted!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertMissing('@moves-card')
        ->assertVisible('@battle-options-card')
        ->assertMissing('@moves-card')
        ->assertSeeIn('@pokemon-fainted-card__title', 'Pikachu fainted!')
        ->assertMissing('@send-out-sheet')
        ->assertButtonDisabled('@battle-team-rail__send-out-button')
        ->click('@battle-team-rail__pokemon-2-radio')
        ->assertButtonEnabled('@battle-team-rail__send-out-button')
        ->click('@battle-team-rail__send-out-button')
        ->wait(1)
        ->assertMissing('@pokemon-fainted-card')
        ->assertVisible('@moves-card')
        ->assertSeeIn('@battlefield-combatant-card--player', 'Eevee')
        ->assertSeeIn('@battle-log-card', 'Go! Eevee!')
        ->assertNoJavascriptErrors();
});

it('sends out another pokemon from a sheet that cannot be dismissed on a phone', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'current_hp' => 0]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: true,
            log: [new BattleLogEntry(3, BattleLogKind::EVENT, 'Pikachu fainted!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(390, 844)
        ->assertVisible('@send-out-sheet')
        ->keys('@send-out-sheet', 'Escape')
        ->assertScript(<<<'JS'
            (() => {
                document.querySelector('[data-slot="sheet-overlay"]').dispatchEvent(new PointerEvent('pointerdown', { bubbles: true }));

                return true;
            })()
            JS)
        ->wait(0.5)
        ->assertVisible('@send-out-sheet')
        ->assertButtonDisabled('@send-out-sheet__send-out-button')
        ->click('@send-out-sheet__pokemon-2-radio')
        ->click('@send-out-sheet__send-out-button')
        ->wait(1)
        ->assertMissing('@send-out-sheet')
        ->assertSeeIn('@battlefield-combatant-card--player', 'Eevee')
        ->assertNoJavascriptErrors();
});

it('switches from the team rail to another pokemon, which the opponent then attacks', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $bite    = Move::factory()->plainDamage()->create(['name' => 'Bite', 'type' => PokemonType::DARK, 'power' => 60]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'level' => 54, 'boost' => 72, 'types' => [PokemonType::NORMAL], 'move_ids' => [$bite->id], 'max_hp' => 180, 'current_hp' => 180, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            ],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, false, 1.0]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertSeeIn('@battle-team-rail__in-battle-list', 'Pikachu')
        ->assertNotPresent('@battle-team-rail__pokemon-1-radio')
        ->assertSeeIn('@battle-team-rail__bench-list', 'Eevee')
        ->assertSeeIn('@battle-team-rail__pokemon-2-radio', 'Lv 54')
        ->assertDontSeeIn('@battle-team-rail__pokemon-2-radio', '+72')
        ->assertScript('document.querySelectorAll(\'[data-test="battle-team-rail"] [data-state="checked"]\').length', 0)
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->click('@battle-team-rail__pokemon-2-radio')
        ->assertButtonEnabled('@battle-team-rail__switch-button')
        ->click('@battle-team-rail__switch-button')
        ->wait(1)
        ->assertSeeIn('@battlefield-combatant-card--player', 'Eevee')
        ->assertSeeIn('@battle-team-rail__in-battle-list', 'Eevee')
        ->assertNotPresent('@battle-team-rail__pokemon-2-radio')
        ->assertSeeIn('@battle-team-rail__pokemon-1-radio', 'Pikachu')
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->assertSeeIn('@battle-log-card', 'You switched Pokémon to Eevee.')
        ->assertSeeIn('@battle-log-card', 'The wild Pidgey used Tackle!')
        ->assertSeeIn('@battlefield-combatant-card--player__hp-indicator', '134 / 180')
        ->assertSeeIn('@moves-card', 'Bite')
        ->assertNoJavascriptErrors();
});

it('runs from the battle after confirming', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            ],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->click('@battle-forfeit-button')
        ->assertVisible('@battle-forfeit-button__dialog')
        ->click('@battle-forfeit-button__confirm-button')
        ->assertVisible('@battlefield-card')
        ->wait(1)
        ->assertVisible('@battlefield-card')
        ->assertSeeIn('@battle-result-card__title', 'You got away safely')
        ->assertSeeIn('@battle-result-card__no-experience-empty-state', "No XP this time: the wild Pikachu wasn't knocked out or caught.")
        ->assertSeeIn('@battle-result-card', 'Back to Dashboard')
        ->assertSeeIn('@battle-log-card', 'You got away safely!')
        ->assertNoJavascriptErrors();
});

it('shows the XP each pokemon gained when the battle is won', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 54,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::WON,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'current_hp' => 0])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!')],
            experience_gains: [
                new ExperienceGain(combatant_index: 0, earned_experience: 1_340, level_before: 54, level_after: 55, is_from_experience_share: false),
                new ExperienceGain(combatant_index: 1, earned_experience: 125, level_before: 54, level_after: 54, is_from_experience_share: true),
            ],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertVisible('@battlefield-card')
        ->assertSeeIn('@battle-result-card__title', 'You won!')
        ->assertSeeIn('@battle-result-card', 'The wild Pidgey fainted.')
        ->assertSeeIn('@battle-result-card', 'XP gained')
        ->assertSeeIn('@battle-result-card__xp-gain-1-item', 'Pikachu')
        ->assertSeeIn('@battle-result-card__xp-gain-1-item', '+1,340 XP')
        ->assertSeeIn('@battle-result-card__xp-gain-1-item', 'Level up! 54 → 55')
        ->assertDontSeeIn('@battle-result-card__xp-gain-1-item', 'via XP Share')
        ->assertSeeIn('@battle-result-card__xp-gain-2-item', 'Eevee')
        ->assertSeeIn('@battle-result-card__xp-gain-2-item', '+125 XP')
        ->assertSeeIn('@battle-result-card__xp-gain-2-item', 'via XP Share')
        ->assertDontSeeIn('@battle-result-card__xp-gain-2-item', 'Level up!')
        ->assertMissing('@battle-result-card__no-experience-empty-state')
        ->wait(1)
        ->assertNoAccessibilityIssues()
        ->assertNoJavascriptErrors();
});

it('shows that no XP was gained when the battle is lost', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
        'move_ids'                      => [$tackle->id],
        'max_hp'                        => 218,
        'current_hp'                    => 0,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::LOST,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'current_hp' => 218])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!')],
            experience_gains: [new ExperienceGain(combatant_index: 0, earned_experience: 0, level_before: 50, level_after: 50, is_from_experience_share: false)],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertVisible('@battlefield-card')
        ->assertSeeIn('@battle-result-card__title', 'You lost to the wild Pidgey')
        ->assertSeeIn('@battle-result-card__no-experience-empty-state', "No XP this time: the wild Pidgey wasn't knocked out.")
        ->assertMissing('@battle-result-card__xp-gains-list')
        ->assertNoJavascriptErrors();
});

it('shows three columns on a wide screen and stacks the cards in reading order on a phone', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(1440, 900)
        ->assertScript(<<<'JS'
            (() => {
                const field = document.querySelector('[data-test="battlefield-card"]').getBoundingClientRect();
                const moves = document.querySelector('[data-test="moves-card"]').getBoundingClientRect();
                const log = document.querySelector('[data-test="battle-log-card"]').getBoundingClientRect();

                return field.right <= log.left && field.top === log.top && moves.top >= field.bottom && moves.left === field.left;
            })()
            JS)
        ->resize(390, 844)
        ->assertScript(<<<'JS'
            (() => {
                const field = document.querySelector('[data-test="battlefield-card"]').getBoundingClientRect();
                const moves = document.querySelector('[data-test="moves-card"]').getBoundingClientRect();
                const team = document.querySelector('[data-test="battle-team-rail"]').getBoundingClientRect();
                const options = document.querySelector('[data-test="battle-options-card"]').getBoundingClientRect();
                const log = document.querySelector('[data-test="battle-log-card"]').getBoundingClientRect();

                return moves.top >= field.bottom && team.top >= moves.bottom && options.top >= team.bottom && log.top >= options.bottom && field.left === moves.left && moves.left === log.left;
            })()
            JS)
        ->assertNoJavascriptErrors();
});

it('shows the result below the battlefield on a phone, once the battle has ended', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
            turn: 2,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(390, 844)
        ->assertSeeIn('@battle-header', 'A wild Pikachu appeared · Turn 1')
        ->assertVisible('@battlefield-card')
        ->assertScript(<<<'JS'
            (() => {
                const field = document.querySelector('[data-test="battlefield-card"]').getBoundingClientRect();
                const result = document.querySelector('[data-test="battle-result-card"]').getBoundingClientRect();
                const log = document.querySelector('[data-test="battle-log-card"]').getBoundingClientRect();

                return result.top >= field.bottom && log.top >= result.bottom;
            })()
            JS)
        ->assertNoJavascriptErrors();
});

it('does not switch to the pokemon in battle or a fainted one', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'current_hp' => 0]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    $page = visit(route('battle.web.show'))
        ->assertSeeIn('@battle-team-rail__pokemon-2-radio', 'Fainted');

    $page->script('document.querySelector(\'[data-test="battle-team-rail__pokemon-2-radio"]\').click()');

    $page
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->assertNotPresent('@battle-team-rail__pokemon-1-radio')
        ->assertNoJavascriptErrors();
});

it('shows the team in as many columns as fit below the wide layout', function (int $width, int $columns) {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'current_hp' => 218]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-3', 'species_id' => $eevee->id]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-4', 'species_id' => $eevee->id]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-5', 'species_id' => $eevee->id]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize($width, 900)
        ->assertScript(<<<'JS'
            (() => {
                const members = [...document.querySelectorAll('[data-test="battle-team-rail__bench-list"] [data-test$="-radio"]')].map((member) => member.getBoundingClientRect());

                return members.filter((member) => member.top === members[0].top).length;
            })()
            JS, $columns)
        ->assertNoJavascriptErrors();
})->with([
    'a phone'         => [390, 1],
    'a small tablet'  => [600, 2],
    'a large tablet'  => [1100, 3],
    'the wide layout' => [1440, 1],
]);

it('shows the opponent team rail in a trainer battle, after your team on a phone', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $geodude = Species::factory()->create(['name' => 'Geodude']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'type'   => BattleType::GYM,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $geodude->id]),
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'has_entered_field' => false]),
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'current_hp' => 0]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'The gym leader wants to battle!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(1440, 900)
        ->assertVisible('@opponent-team-rail')
        ->assertSeeIn('@opponent-team-rail__in-battle-list', 'Geodude')
        ->assertSeeIn('@opponent-team-rail__rest-of-the-team-list', 'Not sent out yet')
        ->assertNotPresent('@opponent-team-rail__pokemon-2-details-button')
        ->assertSeeIn('@opponent-team-rail__pokemon-3-item', 'Fainted')
        ->assertDontSeeIn('@opponent-team-rail__pokemon-3-item', 'HP:')
        ->resize(390, 844)
        ->assertVisible('@opponent-team-rail')
        ->assertScript(<<<'JS'
            (() => {
                const team = document.querySelector('[data-test="battle-team-rail"]').getBoundingClientRect();
                const opponentTeam = document.querySelector('[data-test="opponent-team-rail"]').getBoundingClientRect();
                const log = document.querySelector('[data-test="battle-log-card"]').getBoundingClientRect();

                return opponentTeam.top >= team.bottom && log.top >= opponentTeam.bottom;
            })()
            JS)
        ->assertScript('document.querySelector(\'[data-test="battlefield-combatant-card--opponent"] ul\') === null')
        ->assertNoJavascriptErrors();
});

it('does not show the opponent team rail in a wild battle', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(1440, 900)
        ->assertMissing('@opponent-team-rail')
        ->assertNoJavascriptErrors();
});

it('opens the end section and the last played turn once the battle has ended', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
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
        'status' => BattleStatus::WON,
        'state'  => new BattleState(
            turn: 3,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null, 'current_hp' => 0])], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(2, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(2, BattleLogKind::EVENT, 'The wild Pikachu fainted!'),
                new BattleLogEntry(3, BattleLogKind::RESULT, 'You won!'),
            ],
            experience_gains: [new ExperienceGain(combatant_index: 0, earned_experience: 0, level_before: 50, level_after: 50, is_from_experience_share: false)],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertSeeIn('@battle-log-card__turn-3-button', 'End')
        ->assertSeeIn('@battle-log-card__turn-3-line-1', 'You won!')
        ->assertScript("getComputedStyle(document.querySelector('[data-test=\"battle-log-card__turn-3-line-1\"]')).fontWeight", '600')
        ->assertSeeIn('@battle-log-card__turn-2-line-2', 'The wild Pikachu fainted!')
        ->assertMissing('@battle-log-card__turn-1-line-1')
        ->assertMissing('@battle-log-card__turn-0-line-1')
        ->assertNoJavascriptErrors();
});

it('shows a pokemon details in a hover card above its portrait on a wide screen', function () {
    $user         = User::factory()->create();
    $pikachu      = Species::factory()->create(['name' => 'Pikachu']);
    $eevee        = Species::factory()->create(['name' => 'Eevee']);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $growl        = Move::factory()->plainDamage()->create(['name' => 'Growl', 'type' => PokemonType::NORMAL, 'power' => null]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 61,
        'boost'                         => 20,
        'is_shiny'                      => true,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC, PokemonType::STEEL],
        'move_ids'                      => [$thunderShock->id, $growl->id],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'is_shiny' => false, 'boost' => 0, 'types' => [PokemonType::NORMAL], 'move_ids' => [$growl->id]]),
            ], active_index: 1),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $eevee->id])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Eevee appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(1440, 900)
        ->assertMissing('@pokemon-details-hover-card')
        ->assertAttributeMissing('@battle-team-rail__pokemon-1-details-button', 'aria-haspopup')
        ->hover('@battle-team-rail__pokemon-1-details-button')
        ->wait(0.5)
        ->assertVisible('@pokemon-details-hover-card')
        ->assertSeeIn('@pokemon-details-hover-card', 'Shiny Pikachu')
        ->assertSeeIn('@pokemon-details-hover-card', 'Lv 61')
        ->assertSeeIn('@pokemon-details-hover-card', 'Steel')
        ->assertSeeIn('@pokemon-details-hover-card', 'Boost +20 · +20% HP and damage')
        ->assertSeeIn('@pokemon-details-hover-card', 'Shiny · +25% HP and damage, takes 25% less damage')
        ->assertSeeIn('@pokemon-details-hover-card', 'Thunder Shock')
        ->assertSeeIn('@pokemon-details-hover-card', 'Electric · 40')
        ->assertSeeIn('@pokemon-details-hover-card', 'Normal · Status')
        ->assertScript(<<<'JS'
            (() => {
                const portrait = document.querySelector('[data-test="battle-team-rail__pokemon-1-details-button"]').getBoundingClientRect();
                const card = document.querySelector('[data-test="pokemon-details-hover-card"]').getBoundingClientRect();

                return card.bottom <= portrait.top;
            })()
            JS)
        ->assertNoJavascriptErrors();
});

it('shows a pokemon details in a sheet from its portrait on a phone, without picking it', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $bite    = Move::factory()->plainDamage()->create(['name' => 'Bite', 'type' => PokemonType::DARK, 'power' => 60]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'level' => 40, 'types' => [PokemonType::NORMAL], 'move_ids' => [$bite->id], 'max_hp' => 180, 'current_hp' => 90]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(390, 844)
        ->assertAttribute('@battle-team-rail__pokemon-2-details-button', 'aria-haspopup', 'dialog')
        ->click('@battle-team-rail__pokemon-2-details-button')
        ->assertAttribute('@battle-team-rail__pokemon-2-details-button', 'aria-expanded', 'true')
        ->assertVisible('@pokemon-details-sheet')
        ->assertSeeIn('@pokemon-details-sheet', 'Lv 40 · HP 90 / 180')
        ->assertSeeIn('@pokemon-details-sheet', 'Normal')
        ->assertSeeIn('@pokemon-details-sheet', 'Bite')
        ->assertSeeIn('@pokemon-details-sheet', 'Dark · 60')
        ->assertMissing('@pokemon-details-hover-card')
        ->click('@pokemon-details-sheet__close-button')
        ->wait(0.5)
        ->assertMissing('@pokemon-details-sheet')
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->click('@battle-team-rail__pokemon-2-radio')
        ->assertButtonEnabled('@battle-team-rail__switch-button')
        ->assertNoJavascriptErrors();
});

it('clears the bench pick when the pokemon in battle faints or the battle ends', function (int $playerHp, int $opponentHp, string $nextCard) {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
        'move_ids'                      => [$tackle->id],
        'max_hp'                        => 218,
        'current_hp'                    => $playerHp,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'types' => [PokemonType::NORMAL], 'current_hp' => 218, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'current_hp' => $opponentHp]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!')],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(1440, 900)
        ->click('@battle-team-rail__pokemon-2-radio')
        ->assertButtonEnabled('@battle-team-rail__switch-button')
        ->click('@moves-card__attack-button')
        ->wait(1)
        ->assertVisible("@{$nextCard}")
        ->assertScript('document.querySelectorAll(\'[data-test="battle-team-rail"] [data-state="checked"]\').length', 0)
        ->assertNoJavascriptErrors();
})->with([
    'a faint'           => [1, 218, 'pokemon-fainted-card'],
    'the end of battle' => [218, 1, 'battle-result-card'],
]);

it('opens a pokemon details from a tap on its portrait on a wide screen', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
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
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2']),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    $page = visit(route('battle.web.show'))
        ->resize(1440, 900)
        ->assertMissing('@pokemon-details-hover-card');

    $page->script('document.querySelector(\'[data-test="battle-team-rail__pokemon-2-details-button"]\').click()');

    $page
        ->wait(0.5)
        ->assertVisible('@pokemon-details-hover-card')
        ->assertSeeIn('@pokemon-details-hover-card', 'Tackle')
        ->assertAttribute('@battle-team-rail__pokemon-2-details-button', 'aria-expanded', 'true')
        ->assertMissing('@pokemon-details-sheet')
        ->assertNoJavascriptErrors();
});
