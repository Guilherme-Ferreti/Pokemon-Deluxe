<?php

declare(strict_types=1);

use App\Battle\Actions\TakeTurnAction;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\DynamaxMoveNotAllowedException;
use App\Battle\Exceptions\MoveAlreadyUsedException;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
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
            log: ['A wild Pidgey appeared!', 'Go! Pikachu!'],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    $state = new TakeTurnAction()->handle($battle, moveSlot: 2)->fresh()?->state;

    expect($state?->turn)->toBe(2)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218 - 93)
        ->and($state?->player_side->activeCombatant()->current_hp)->toBe(218 - 46)
        ->and($state?->log)->toBe([
            'A wild Pidgey appeared!',
            'Go! Pikachu!',
            'Pikachu used Thunder Shock!',
            "It's super effective!",
            'The wild Pidgey used Tackle!',
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
        ->and($state?->log)->toBe([
            'The wild Pidgey used Tackle!',
            'Shiny Pikachu fainted!',
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
        ->and($state?->log)->toBe([
            'Pikachu used Thunder Shock!',
            "It's super effective!",
            'The wild Pidgey fainted!',
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
        ->and($state?->log)->toBe([
            'Gastly used Lick!',
            "It doesn't affect the wild Pidgey...",
            'The wild Pidgey used Tackle!',
            "It doesn't affect Gastly...",
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
            ->and($state?->log)->toBe([...$expectedPlayerLines, 'The wild Pidgey used Growl!']);
    })->with([
        'miss'          => [[false], 218, ['Pikachu used Tackle!', "Pikachu's attack missed!"]],
        'normal hit'    => [[true, false, 1.0], 218 - 31, ['Pikachu used Tackle!']],
        'critical hit'  => [[true, true, 1.0], 218 - 46, ['Pikachu used Tackle!', 'A critical hit!']],
        'lowest random' => [[true, false, 0.85], 218 - 26, ['Pikachu used Tackle!']],
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
            ->and($state?->log)->toBe($expectedLines);
    })->with([
        'three hits, one critical' => [
            218,
            [true, 3, false, 1.0, true, 1.0, false, 1.0, true],
            218 - 11 - 17 - 11,
            ['Pikachu used Fury Attack!', 'A critical hit!', 'It hit 3 times!', 'The wild Pidgey used Growl!'],
        ],
        'faints after the first hit' => [
            5,
            [true, 3, false, 1.0, false, 1.0, false, 1.0],
            0,
            ['Pikachu used Fury Attack!', 'It hit once!', 'The wild Pidgey fainted!'],
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
            ->and($state?->log)->toBe($expectedLines);
    })->with([
        'player first: the opponent flinches' => [
            true,
            [true, false, 1.0, true],
            218,
            ['Pikachu used Headbutt!', 'The wild Pidgey flinched and couldn\'t move!'],
        ],
        'player second: too late to flinch' => [
            false,
            [true, false, 1.0, true, false, 1.0, true],
            218 - 46,
            ['The wild Pidgey used Tackle!', 'Pikachu used Headbutt!'],
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
            ->toThrow(MoveAlreadyUsedException::class);
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
