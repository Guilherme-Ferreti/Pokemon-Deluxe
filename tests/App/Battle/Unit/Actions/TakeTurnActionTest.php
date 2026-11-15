<?php

declare(strict_types=1);

use App\Battle\Actions\TakeTurnAction;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
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
    $thunderShock = Move::factory()->create(['name' => 'Thunder Shock', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->create(['name' => 'Tackle', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 40]);

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

    RandomHelper::fake([$tackle->id, true]);

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
    $thunderShock = Move::factory()->create(['name' => 'Thunder Shock', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->create(['name' => 'Tackle', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 40]);

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

    RandomHelper::fake([$tackle->id, false]);

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
    $thunderShock = Move::factory()->create(['name' => 'Thunder Shock', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->create(['name' => 'Tackle', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 40]);

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

    RandomHelper::fake([$tackle->id, true]);

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
    $tackle = Move::factory()->create(['name' => 'Tackle', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 40]);
    $lick   = Move::factory()->create(['name' => 'Lick', 'category' => MoveCategory::DAMAGE, 'type' => PokemonType::GHOST, 'power' => 30]);

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

    RandomHelper::fake([$tackle->id, true]);

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
    $tackle = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 40]);

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
    $tackle = Move::factory()->create(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 40]);

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
