<?php

declare(strict_types=1);

use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
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
        ->assertSeeIn('@moves-card__move-2-button', 'Super effective')
        ->click('@moves-card__move-2-button')
        ->wait(1)
        ->assertSeeIn('@battlefield-combatant-card--opponent__hp-indicator', '125 / 218')
        ->assertSeeIn('@battlefield-combatant-card--player__hp-indicator', '172 / 218')
        ->assertSeeIn('@battle-log-card', 'Pikachu used Thunder Shock!')
        ->assertSeeIn('@battle-log-card', 'The wild Pidgey used Tackle!')
        ->assertNoJavascriptErrors();
});

it('sends out another pokemon after a faint', function () {
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
        ->assertVisible('@send-out-dialog')
        ->click('@send-out-dialog__pokemon-2-radio')
        ->click('@send-out-dialog__send-out-button')
        ->wait(1)
        ->assertMissing('@send-out-dialog')
        ->assertSeeIn('@battle-log-card', 'Go! Eevee!')
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
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'types' => [PokemonType::NORMAL], 'move_ids' => [$bite->id], 'max_hp' => 180, 'current_hp' => 180, 'has_entered_field' => false]),
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
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->click('@battle-team-rail__pokemon-2-radio')
        ->assertButtonEnabled('@battle-team-rail__switch-button')
        ->click('@battle-team-rail__switch-button')
        ->wait(1)
        ->assertSeeIn('@battlefield-combatant-card--player', 'Eevee')
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->assertSeeIn('@battle-log-card', 'Come back, Pikachu!')
        ->assertSeeIn('@battle-log-card', 'Go! Eevee!')
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
        ->click('@run-battle-dialog__open-button')
        ->assertVisible('@run-battle-dialog')
        ->click('@run-battle-dialog__run-button')
        ->assertVisible('@battlefield-card')
        ->wait(2)
        ->assertMissing('@battlefield-card')
        ->assertSeeIn('@battle-result-card', 'You got away safely')
        ->assertSeeIn('@battle-log-card', 'You got away safely!')
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
                const log = document.querySelector('[data-test="battle-log-card"]').getBoundingClientRect();

                return moves.top >= field.bottom && log.top >= moves.bottom && field.left === moves.left && moves.left === log.left;
            })()
            JS)
        ->assertNoJavascriptErrors();
});

it('shows the result in place of the battlefield, first on a phone, once the battle has ended', function () {
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
        ->assertMissing('@battlefield-card')
        ->assertScript(<<<'JS'
            (() => {
                const result = document.querySelector('[data-test="battle-result-card"]').getBoundingClientRect();
                const log = document.querySelector('[data-test="battle-log-card"]').getBoundingClientRect();

                return log.top >= result.bottom;
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

    visit(route('battle.web.show'))
        ->assertSeeIn('@battle-team-rail__pokemon-2-radio', 'Fainted')
        ->click('@battle-team-rail__pokemon-2-radio')
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->click('@battle-team-rail__pokemon-1-radio')
        ->assertButtonDisabled('@battle-team-rail__switch-button')
        ->assertNoJavascriptErrors();
});

it('shows the team as a grid of two columns on a phone', function () {
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
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pikachu appeared!')],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->resize(390, 844)
        ->assertScript(<<<'JS'
            (() => {
                const first = document.querySelector('[data-test="battle-team-rail__pokemon-1-radio"]').getBoundingClientRect();
                const second = document.querySelector('[data-test="battle-team-rail__pokemon-2-radio"]').getBoundingClientRect();

                return first.top === second.top && second.left >= first.right;
            })()
            JS)
        ->assertNoJavascriptErrors();
});
