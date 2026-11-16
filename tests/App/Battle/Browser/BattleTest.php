<?php

declare(strict_types=1);

use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\User;
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
            log: ['A wild Pikachu appeared!', 'Go! Pikachu!'],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Wild battle')
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
            log: ['A wild Pidgey appeared!', 'Go! Pikachu!'],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->assertSeeIn('@combatant-panel--opponent__hp-indicator', '100%')
        ->assertSeeIn('@moves-card__move-2-button', 'Super effective')
        ->click('@moves-card__move-2-button')
        ->wait(1)
        ->assertSeeIn('@combatant-panel--opponent__hp-indicator', '58%')
        ->assertSeeIn('@combatant-panel--player__hp-indicator', '172 / 218')
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
            log: ['Pikachu fainted!'],
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

it('switches to another pokemon, which the opponent then attacks', function () {
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
            log: ['A wild Pidgey appeared!', 'Go! Pikachu!'],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, false, 1.0]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->click('@switch-pokemon-dialog__open-button')
        ->assertVisible('@switch-pokemon-dialog')
        ->click('@switch-pokemon-dialog__pokemon-2-radio')
        ->click('@switch-pokemon-dialog__switch-button')
        ->wait(1)
        ->assertMissing('@switch-pokemon-dialog')
        ->assertSeeIn('@battle-log-card', 'Come back, Pikachu!')
        ->assertSeeIn('@battle-log-card', 'Go! Eevee!')
        ->assertSeeIn('@battle-log-card', 'The wild Pidgey used Tackle!')
        ->assertSeeIn('@combatant-panel--player__hp-indicator', '134 / 180')
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
            log: ['A wild Pikachu appeared!', 'Go! Pikachu!'],
        ),
    ]);

    actingAs($user);

    visit(route('battle.web.show'))
        ->click('@run-battle-dialog__open-button')
        ->assertVisible('@run-battle-dialog')
        ->click('@run-battle-dialog__run-button')
        ->wait(1)
        ->assertSeeIn('@battle-result-card', 'You got away safely')
        ->assertSeeIn('@battle-log-card', 'You ran away!')
        ->assertNoJavascriptErrors();
});
