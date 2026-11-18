<?php

declare(strict_types=1);

use App\Battle\Actions\TakeTurnAction;
use App\Battle\Events\BattleEnded;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\DynamaxMoveNotAllowedException;
use App\Battle\Exceptions\OncePerBattleMoveAlreadyUsedException;
use Illuminate\Support\Facades\Event;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

it('lets both sides deal damage, the player first, and logs it', function () {
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: $pikachu->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                move_ids: [$tackle->id, $thunderShock->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: null,
                species_id: $pidgey->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL, PokemonType::FLYING],
                move_ids: [$tackle->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            ],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    $state = new TakeTurnAction()->handle($battle, moveSlot: 2)->fresh()?->state;

    expect($state?->turn)->toBe(2)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218 - 93)
        ->and($state?->player_side->activeCombatant()->current_hp)->toBe(218 - 46)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
            new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Thunder Shock!', 'Thunder Shock'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, "It's super effective!"),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 93 HP damage.'),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
        ]);
});

it('lets the opponent move first when the turn order roll fails, and a fainted player does not act', function () {
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 3,
            player_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: $pikachu->id,
                level: 50,
                boost: 0,
                is_shiny: true,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                move_ids: [$thunderShock->id],
                max_hp: 273,
                current_hp: 30,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: null,
                species_id: $pidgey->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL, PokemonType::FLYING],
                move_ids: [$tackle->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$tackle->id, false, true, false, 1.0]);

    $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

    expect($state?->player_side->activeCombatant()->current_hp)->toBe(0)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218)
        ->and($state?->turn)->toBe(4)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(3, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(3, BattleLogKind::DAMAGE, 'It dealt 30 HP damage.'),
            new BattleLogEntry(3, BattleLogKind::EVENT, 'Shiny Pikachu fainted!'),
            new BattleLogEntry(4, BattleLogKind::RESULT, 'You lost...'),
        ]);
});

it('does not let a fainted opponent act', function () {
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: $pikachu->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                move_ids: [$thunderShock->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: null,
                species_id: $pidgey->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL, PokemonType::FLYING],
                move_ids: [$tackle->id],
                max_hp: 218,
                current_hp: 10,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0]);

    $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

    expect($state?->opponent_side->activeCombatant()->current_hp)->toBe(0)
        ->and($state?->player_side->activeCombatant()->current_hp)->toBe(218)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Thunder Shock!', 'Thunder Shock'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, "It's super effective!"),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 10 HP damage.'),
            new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey fainted!'),
            new BattleLogEntry(2, BattleLogKind::RESULT, 'You won!'),
        ]);
});

it('logs a move that does not affect the defender and deals no damage', function () {
    $gastly = Species::factory()->create(['name' => 'Gastly', 'types' => [PokemonType::GHOST, PokemonType::POISON]]);
    $pidgey = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $tackle = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $lick   = Move::factory()->plainDamage()->create(['name' => 'Lick', 'type' => PokemonType::GHOST, 'power' => 30]);

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: $gastly->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::GHOST, PokemonType::POISON],
                move_ids: [$lick->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: null,
                species_id: $pidgey->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL, PokemonType::FLYING],
                move_ids: [$tackle->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

    expect($state?->player_side->activeCombatant()->current_hp)->toBe(218)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Gastly used Lick!', 'Lick'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, "It doesn't affect the wild Pidgey..."),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, "It doesn't affect Gastly..."),
        ]);
});

it('refuses a turn when the battle is over or awaiting a send-out', function (BattleStatus $status, bool $isAwaitingSendOut) {
    $tackle = Move::factory()->plainDamage()->create(['type' => PokemonType::NORMAL, 'power' => 40]);

    $state = new BattleState(
        turn: 5,
        player_side: new BattleSide(combatants: [new Combatant(
            pokemon_id: 'pokemon-1',
            species_id: 'species-1',
            level: 50,
            boost: 0,
            is_shiny: false,
            gender: PokemonGender::MALE,
            types: [PokemonType::NORMAL],
            move_ids: [$tackle->id],
            max_hp: 218,
            current_hp: 218,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: true,
            used_once_per_battle_move_ids: [],
        )], active_index: 0),
        opponent_side: new BattleSide(combatants: [new Combatant(
            pokemon_id: null,
            species_id: 'species-2',
            level: 50,
            boost: 0,
            is_shiny: false,
            gender: PokemonGender::FEMALE,
            types: [PokemonType::NORMAL],
            move_ids: [$tackle->id],
            max_hp: 218,
            current_hp: 218,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: true,
            used_once_per_battle_move_ids: [],
        )], active_index: 0),
        is_awaiting_send_out: $isAwaitingSendOut,
        log: [],
    );

    $battle = Battle::factory()->create(['status' => $status, 'state' => $state]);

    RandomHelper::fake([]);

    expect(fn () => new TakeTurnAction()->handle($battle, moveSlot: 1))
        ->toThrow(BattleNotReadyForTurnException::class)
        ->and($battle->fresh()?->state->equals($state))->toBeTrue();
})->with([
    'won'                 => [BattleStatus::WON, false],
    'lost'                => [BattleStatus::LOST, false],
    'forfeited'           => [BattleStatus::FORFEITED, false],
    'awaiting a send-out' => [BattleStatus::ONGOING, true],
]);

it('rejects a move slot the active pokemon does not have', function (int $moveSlot) {
    $tackle = Move::factory()->plainDamage()->create(['type' => PokemonType::NORMAL, 'power' => 40]);

    $battle = Battle::factory()->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: 'species-1',
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::NORMAL],
                move_ids: [$tackle->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(
                pokemon_id: null,
                species_id: 'species-2',
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL],
                move_ids: [$tackle->id],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            )], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([]);

    expect(fn () => new TakeTurnAction()->handle($battle, moveSlot: $moveSlot))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'slot 0' => [0],
    'slot 2' => [2],
]);

describe('move outcomes', function () {
    it('applies a miss, a critical hit or a low random factor to the player hit', function (array $playerRolls, int $expectedOpponentHp, array $expectedPlayerLines) {
        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40, 'accuracy' => 95]);
        $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, true, ...array_values($playerRolls), true]);

        $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

        expect($state?->opponent_side->activeCombatant()->current_hp)->toBe($expectedOpponentHp)
            ->and($state?->log)->toEqual([...$expectedPlayerLines, new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl')]);
    })->with([
        'miss' => [[false], 218, [
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, 'It missed...'),
        ]],
        'normal hit' => [[true, false, 1.0], 218 - 31, [
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
        ]],
        'critical hit' => [[true, true, 1.0], 218 - 46, [
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, 'A critical hit!'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
        ]],
        'lowest random' => [[true, false, 0.85], 218 - 26, [
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 26 HP damage.'),
        ]],
    ]);

    it('deals each hit of a multi-hit move separately and stops when the defender faints', function (int $opponentHp, array $playerRolls, int $expectedOpponentHp, array $expectedLines) {
        $pikachu    = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey     = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $furyAttack = Move::factory()->plainDamage()->create(['name' => 'Fury Attack', 'type' => PokemonType::NORMAL, 'power' => 15, 'accuracy' => 85, 'min_hits' => 2, 'max_hits' => 5]);
        $growl      = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$furyAttack->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 218,
                    current_hp: $opponentHp,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, true, ...array_values($playerRolls)]);

        $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

        expect($state?->opponent_side->activeCombatant()->current_hp)->toBe($expectedOpponentHp)
            ->and($state?->log)->toEqual($expectedLines);
    })->with([
        'three hits, one critical' => [
            218,
            [true, 3, false, 1.0, true, 1.0, false, 1.0, true],
            218 - 11 - 17 - 11,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Fury Attack!', 'Fury Attack'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, 'A critical hit!'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, 'It hit 3 times!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 39 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'faints after the first hit' => [
            5,
            [true, 3, false, 1.0, false, 1.0, false, 1.0],
            0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Fury Attack!', 'Fury Attack'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, 'It hit once!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 5 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey fainted!'),
                new BattleLogEntry(2, BattleLogKind::RESULT, 'You won!'),
            ],
        ],
    ]);
});

describe('flinch', function () {
    it('makes the defender flinch only when the attacker moved first', function (bool $isPlayerFirst, array $rolls, int $expectedPlayerHp, array $expectedLines) {
        $pikachu  = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey   = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $headbutt = Move::factory()->plainDamage()->create(['name' => 'Headbutt', 'type' => PokemonType::NORMAL, 'power' => 70, 'flinch_chance' => 30]);
        $tackle   = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$headbutt->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$tackle->id, $isPlayerFirst, ...array_values($rolls)]);

        $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

        expect($state?->player_side->activeCombatant()->current_hp)->toBe($expectedPlayerHp)
            ->and($state?->log)->toEqual($expectedLines);
    })->with([
        'player first: the opponent flinches' => [
            true,
            [true, false, 1.0, true],
            218,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Headbutt!', 'Headbutt'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 54 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, "The wild Pidgey flinched and couldn't move!"),
            ],
        ],
        'player second: too late to flinch' => [
            false,
            [true, false, 1.0, true, false, 1.0, true],
            218 - 46,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Headbutt!', 'Headbutt'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 54 HP damage.'),
            ],
        ],
    ]);
});

describe('once-per-battle moves', function () {
    it('marks a Z-Move as used and refuses it for the rest of the battle', function () {
        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $zMove   = Move::factory()->plainDamage()->create(['name' => 'Gigavolt Havoc', 'type' => PokemonType::ELECTRIC, 'power' => 1, 'is_z_move' => true]);
        $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 100,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$zMove->id],
                    max_hp: 400,
                    current_hp: 400,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 100,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 400,
                    current_hp: 400,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, true, true, false, 1.0, true]);

        $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

        expect($state?->player_side->activeCombatant()->used_once_per_battle_move_ids)->toBe([$zMove->id]);

        RandomHelper::fake([]);

        expect(fn () => new TakeTurnAction()->handle($battle, moveSlot: 1))
            ->toThrow(OncePerBattleMoveAlreadyUsedException::class);
    });

    it('lets a G-Max pokemon use a Dynamax move once', function () {
        $charizard   = Species::factory()->create(['name' => 'Charizard', 'types' => [PokemonType::FIRE, PokemonType::FLYING], 'is_gmax' => true]);
        $pidgey      = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $dynamaxMove = Move::factory()->plainDamage()->create(['name' => 'Max Flare', 'type' => PokemonType::FIRE, 'power' => 1, 'is_dynamax_move' => true]);
        $growl       = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $charizard->id,
                    level: 100,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::FIRE, PokemonType::FLYING],
                    move_ids: [$dynamaxMove->id],
                    max_hp: 400,
                    current_hp: 400,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 100,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 400,
                    current_hp: 400,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, true, true, false, 1.0, true]);

        $state = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;

        expect($state?->player_side->activeCombatant()->used_once_per_battle_move_ids)->toBe([$dynamaxMove->id]);
    });

    it('refuses a Dynamax move for a pokemon that is not G-Max', function () {
        $charizard   = Species::factory()->create(['name' => 'Charizard', 'types' => [PokemonType::FIRE, PokemonType::FLYING], 'is_gmax' => false]);
        $pidgey      = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $dynamaxMove = Move::factory()->plainDamage()->create(['name' => 'Max Flare', 'type' => PokemonType::FIRE, 'power' => 1, 'is_dynamax_move' => true]);
        $growl       = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $charizard->id,
                    level: 100,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::FIRE, PokemonType::FLYING],
                    move_ids: [$dynamaxMove->id],
                    max_hp: 400,
                    current_hp: 400,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 100,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 400,
                    current_hp: 400,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([]);

        expect(fn () => new TakeTurnAction()->handle($battle, moveSlot: 1))
            ->toThrow(DynamaxMoveNotAllowedException::class);
    });

    it('never picks an opponent move it can no longer use', function () {
        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
        $zMove   = Move::factory()->plainDamage()->create(['name' => 'Breakneck Blitz', 'type' => PokemonType::NORMAL, 'power' => 1, 'is_z_move' => true]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$zMove->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [$zMove->id],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([]);

        expect(fn () => new TakeTurnAction()->handle($battle, moveSlot: 1))
            ->toThrow(LogicException::class, 'The opponent has no move it can use.');
    });
});

describe('status effects', function () {
    it('checks the player status effects before it acts', function (?Ailment $ailment, int $ailmentTurnsLeft, int $confusionTurnsLeft, array $playerRolls, int $expectedPlayerHp, ?Ailment $expectedAilment, int $expectedAilmentTurnsLeft, int $expectedConfusionTurnsLeft, int $expectedOpponentHp, array $expectedLog) {
        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
        $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: $ailment,
                    ailment_turns_left: $ailmentTurnsLeft,
                    confusion_turns_left: $confusionTurnsLeft,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, true, ...array_values($playerRolls), true]);

        $state  = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;
        $player = $state?->player_side->activeCombatant();

        expect($player?->current_hp)->toBe($expectedPlayerHp)
            ->and($player?->ailment)->toBe($expectedAilment)
            ->and($player?->ailment_turns_left)->toBe($expectedAilmentTurnsLeft)
            ->and($player?->confusion_turns_left)->toBe($expectedConfusionTurnsLeft)
            ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe($expectedOpponentHp)
            ->and($state?->log)->toEqual($expectedLog);
    })->with([
        'stays frozen' => [
            Ailment::FREEZE, 0, 0, [false], 218, Ailment::FREEZE, 0, 0, 218,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is frozen solid!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'thaws out and acts' => [
            Ailment::FREEZE, 0, 0, [true, true, false, 1.0], 218, null, 0, 0, 218 - 31,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu thawed out!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'stays asleep and counts down' => [
            Ailment::SLEEP, 2, 0, [], 218, Ailment::SLEEP, 1, 0, 218,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is fast asleep.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'wakes up and acts' => [
            Ailment::SLEEP, 0, 0, [true, false, 1.0], 218, null, 0, 0, 218 - 31,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu woke up!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'fully paralyzed' => [
            Ailment::PARALYSIS, 0, 0, [true], 218, Ailment::PARALYSIS, 0, 0, 218,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, "Pikachu is paralyzed! It can't move!"),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'acts through paralysis' => [
            Ailment::PARALYSIS, 0, 0, [false, true, false, 1.0], 218, Ailment::PARALYSIS, 0, 0, 218 - 31,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'hurts itself in confusion' => [
            null, 0, 2, [true], 218 - 31, null, 0, 1, 218,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is confused!'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'It hurt itself in its confusion!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'acts while confused' => [
            null, 0, 2, [false, true, false, 1.0], 218, null, 0, 1, 218 - 31,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is confused!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'snaps out of confusion after its last confused turn' => [
            null, 0, 1, [false, true, false, 1.0], 218, null, 0, 0, 218 - 31,
            [
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is confused!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu snapped out of its confusion!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ],
        'burned: deals half damage and is hurt at the end of the turn' => [
            Ailment::BURN, 0, 0, [true, false, 1.0], 218 - 13, Ailment::BURN, 0, 0, 218 - 15,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 15 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is hurt by its burn!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 13 HP damage.'),
            ],
        ],
    ]);

    it('inflicts the ailment of the player move when the target can get it', function (array $moveAttributes, array $opponentTypes, ?Ailment $opponentAilment, array $playerRolls, int $expectedOpponentHp, ?Ailment $expectedAilment, int $expectedAilmentTurnsLeft, int $expectedConfusionTurnsLeft, array $expectedPlayerLines) {
        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => $opponentTypes]);
        $move    = Move::factory()->plainDamage()->create($moveAttributes);
        $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$move->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: array_values($opponentTypes),
                    move_ids: [$growl->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: $opponentAilment,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, false, true, ...array_values($playerRolls)]);

        $state    = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh()?->state;
        $opponent = $state?->opponent_side->activeCombatant();

        expect($opponent?->current_hp)->toBe($expectedOpponentHp)
            ->and($opponent?->ailment)->toBe($expectedAilment)
            ->and($opponent?->ailment_turns_left)->toBe($expectedAilmentTurnsLeft)
            ->and($opponent?->confusion_turns_left)->toBe($expectedConfusionTurnsLeft)
            ->and($state?->log)->toEqual([new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'), ...$expectedPlayerLines]);
    })->with([
        'paralysis from a status move' => [
            ['name' => 'Thunder Wave', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::ELECTRIC, 'power' => null, 'ailment' => Ailment::PARALYSIS],
            [PokemonType::NORMAL, PokemonType::FLYING], null, [true, true], 218, Ailment::PARALYSIS, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Thunder Wave!', 'Thunder Wave'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey is paralyzed! It may be unable to move!'),
            ],
        ],
        'a status move against a type its move type cannot affect' => [
            ['name' => 'Thunder Wave', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::ELECTRIC, 'power' => null, 'ailment' => Ailment::PARALYSIS],
            [PokemonType::GROUND], null, [true, true], 218, null, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Thunder Wave!', 'Thunder Wave'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, "It doesn't affect the wild Pidgey..."),
            ],
        ],
        'a type immune to the ailment' => [
            ['name' => 'Will-O-Wisp', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::FIRE, 'power' => null, 'ailment' => Ailment::BURN],
            [PokemonType::FIRE], null, [true, true], 218, null, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Will-O-Wisp!', 'Will-O-Wisp'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, 'But it failed!'),
            ],
        ],
        'a target that already has an ailment' => [
            ['name' => 'Thunder Wave', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::ELECTRIC, 'power' => null, 'ailment' => Ailment::PARALYSIS],
            [PokemonType::NORMAL], Ailment::POISON, [true, true], 218 - 27, Ailment::POISON, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Thunder Wave!', 'Thunder Wave'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, 'But it failed!'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey is hurt by poison!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 27 HP damage.'),
            ],
        ],
        'sleep for a rolled number of turns' => [
            ['name' => 'Hypnosis', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::PSYCHIC, 'power' => null, 'ailment' => Ailment::SLEEP],
            [PokemonType::NORMAL], null, [true, true, 2], 218, Ailment::SLEEP, 2, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Hypnosis!', 'Hypnosis'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey fell asleep!'),
            ],
        ],
        'confusion for a rolled number of turns' => [
            ['name' => 'Supersonic', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null, 'ailment' => Ailment::CONFUSION],
            [PokemonType::NORMAL], null, [true, true, 3], 218, null, 0, 3,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Supersonic!', 'Supersonic'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey became confused!'),
            ],
        ],
        'burn from a damaging move' => [
            ['name' => 'Ember', 'category' => MoveCategory::DAMAGE_AILMENT, 'type' => PokemonType::FIRE, 'power' => 40, 'ailment' => Ailment::BURN, 'ailment_chance' => 10],
            [PokemonType::NORMAL, PokemonType::FLYING], null, [true, false, 1.0, true], 218 - 31 - 13, Ailment::BURN, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Ember!', 'Ember'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 31 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey was burned!'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey is hurt by its burn!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 13 HP damage.'),
            ],
        ],
        'poison from a status move' => [
            ['name' => 'Poison Powder', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::POISON, 'power' => null, 'ailment' => Ailment::POISON],
            [PokemonType::NORMAL], null, [true, true], 218 - 27, Ailment::POISON, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Poison Powder!', 'Poison Powder'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey was poisoned!'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey is hurt by poison!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 27 HP damage.'),
            ],
        ],
        'freeze from a damaging move' => [
            ['name' => 'Ice Beam', 'category' => MoveCategory::DAMAGE_AILMENT, 'type' => PokemonType::ICE, 'power' => 90, 'ailment' => Ailment::FREEZE, 'ailment_chance' => 10],
            [PokemonType::NORMAL], null, [true, false, 1.0, true], 218 - 70, Ailment::FREEZE, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Ice Beam!', 'Ice Beam'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 70 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey was frozen solid!'),
            ],
        ],
        'a damaging move that is not very effective and whose ailment roll fails' => [
            ['name' => 'Ember', 'category' => MoveCategory::DAMAGE_AILMENT, 'type' => PokemonType::FIRE, 'power' => 40, 'ailment' => Ailment::BURN, 'ailment_chance' => 10],
            [PokemonType::WATER], null, [true, false, 1.0, false], 218 - 15, null, 0, 0,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Ember!', 'Ember'),
                new BattleLogEntry(1, BattleLogKind::DETAIL, "It's not very effective..."),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 15 HP damage.'),
            ],
        ],
    ]);

    it('deals end-of-turn damage in move order and stops once a side has no pokemon left', function (bool $isPlayerFirst, int $currentHp, int $expectedPlayerHp, int $expectedOpponentHp, BattleStatus $expectedStatus, array $expectedLog) {
        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$growl->id],
                    max_hp: 218,
                    current_hp: $currentHp,
                    ailment: Ailment::BURN,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$growl->id],
                    max_hp: 218,
                    current_hp: $currentHp,
                    ailment: Ailment::POISON,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$growl->id, $isPlayerFirst, true, true]);

        $battle = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh();
        $state  = $battle?->state;

        expect($battle?->status)->toBe($expectedStatus)
            ->and($state?->player_side->activeCombatant()->current_hp)->toBe($expectedPlayerHp)
            ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe($expectedOpponentHp)
            ->and($state?->log)->toEqual($expectedLog);
    })->with([
        'both are hurt, the player first' => [
            true, 218, 218 - 13, 218 - 27, BattleStatus::ONGOING,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is hurt by its burn!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 13 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey is hurt by poison!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 27 HP damage.'),
            ],
        ],
        'the player moved first and faints, so the opponent is not hurt' => [
            true, 5, 0, 5, BattleStatus::LOST,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu is hurt by its burn!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 5 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'Pikachu fainted!'),
                new BattleLogEntry(2, BattleLogKind::RESULT, 'You lost...'),
            ],
        ],
        'the opponent moved first and faints, so the player is not hurt' => [
            false, 5, 5, 0, BattleStatus::WON,
            [
                new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Growl!', 'Growl'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey is hurt by poison!'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 5 HP damage.'),
                new BattleLogEntry(1, BattleLogKind::EVENT, 'The wild Pidgey fainted!'),
                new BattleLogEntry(2, BattleLogKind::RESULT, 'You won!'),
            ],
        ],
    ]);
});

describe('fainting and ending', function () {
    it('ends the battle as won once the opponent has no pokemon left', function () {
        Event::fake([BattleEnded::class]);

        $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
        $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: $pikachu->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::ELECTRIC],
                    move_ids: [$thunderShock->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 10,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$tackle->id, true, true, false, 1.0]);

        $battle = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh();

        expect($battle?->status)->toBe(BattleStatus::WON)
            ->and($battle?->state->is_awaiting_send_out)->toBeFalse();

        Event::assertDispatchedTimes(BattleEnded::class, 1);
        Event::assertDispatched(BattleEnded::class, fn (BattleEnded $event) => $event->status === BattleStatus::WON && $event->isWin);
    });

    it('credits the knockout to the player active pokemon, however the opponent fainted', function (?Ailment $opponentAilment, array $rolls) {
        Event::fake([BattleEnded::class]);

        $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
        $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

        $pikachuCombatant = [
            'pokemon_id'                    => 'pokemon-1',
            'species_id'                    => $pikachu->id,
            'level'                         => 50,
            'boost'                         => 0,
            'is_shiny'                      => false,
            'gender'                        => PokemonGender::MALE,
            'types'                         => [PokemonType::ELECTRIC],
            'move_ids'                      => [$thunderShock->id],
            'max_hp'                        => 218,
            'current_hp'                    => 218,
            'ailment'                       => null,
            'ailment_turns_left'            => 0,
            'confusion_turns_left'          => 0,
            'has_entered_field'             => true,
            'used_once_per_battle_move_ids' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: [
                    new Combatant(...[...$pikachuCombatant, 'pokemon_id' => 'pokemon-0', 'has_entered_field' => false]),
                    new Combatant(...$pikachuCombatant),
                ], active_index: 1),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 10,
                    ailment: $opponentAilment,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake(array_values([$tackle->id, ...$rolls]));

        $battle = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh();

        expect($battle?->status)->toBe(BattleStatus::WON)
            ->and($battle?->state->opponent_side->combatants[0]->knocked_out_by)->toBe(1);
    })->with([
        'by a move'                  => [null, [true, true, false, 1.0]],
        'by its burn after the turn' => [Ailment::BURN, [true, false, true, false, 1.0]],
    ]);

    it('awaits a send-out when the player active pokemon faints, or ends as lost when none are left', function (bool $hasPokemonLeft, BattleStatus $expectedStatus, bool $expectedIsAwaitingSendOut, int $expectedLossEvents) {
        Event::fake([BattleEnded::class]);

        $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
        $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
        $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

        $activePikachu = new Combatant(
            pokemon_id: 'pokemon-1',
            species_id: $pikachu->id,
            level: 50,
            boost: 0,
            is_shiny: false,
            gender: PokemonGender::MALE,
            types: [PokemonType::ELECTRIC],
            move_ids: [$tackle->id],
            max_hp: 218,
            current_hp: 10,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: true,
            used_once_per_battle_move_ids: [],
        );
        $benchPikachu = new Combatant(
            pokemon_id: 'pokemon-2',
            species_id: $pikachu->id,
            level: 50,
            boost: 0,
            is_shiny: false,
            gender: PokemonGender::FEMALE,
            types: [PokemonType::ELECTRIC],
            move_ids: [$tackle->id],
            max_hp: 218,
            current_hp: 218,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: false,
            used_once_per_battle_move_ids: [],
        );

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 1,
                player_side: new BattleSide(combatants: $hasPokemonLeft ? [$activePikachu, $benchPikachu] : [$activePikachu], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: $pidgey->id,
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL, PokemonType::FLYING],
                    move_ids: [$tackle->id],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        RandomHelper::fake([$tackle->id, false, true, false, 1.0]);

        $battle = new TakeTurnAction()->handle($battle, moveSlot: 1)->fresh();

        expect($battle?->status)->toBe($expectedStatus)
            ->and($battle?->state->is_awaiting_send_out)->toBe($expectedIsAwaitingSendOut)
            ->and(Event::dispatched(BattleEnded::class)->count())->toBe($expectedLossEvents)
            ->and(Event::dispatched(BattleEnded::class, fn (BattleEnded $event) => $event->status === BattleStatus::LOST && ! $event->isWin)->count())->toBe($expectedLossEvents);
    })->with([
        'pokemon left: awaits a send-out' => [true, BattleStatus::ONGOING, true, 0],
        'none left: lost'                 => [false, BattleStatus::LOST, false, 1],
    ]);
});
