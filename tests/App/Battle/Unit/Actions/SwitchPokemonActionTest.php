<?php

declare(strict_types=1);

use App\Battle\Actions\SwitchPokemonAction;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\InvalidSwitchException;
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

it('switches in the chosen pokemon, which the opponent then attacks', function () {
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee        = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => true,
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
            turn: 3,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'ailment' => Ailment::PARALYSIS, 'confusion_turns_left' => 2]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'is_shiny' => false, 'types' => [PokemonType::NORMAL], 'move_ids' => [$tackle->id], 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'is_shiny' => false, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'move_ids' => [$tackle->id]]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Shiny Pikachu!'),
            ],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, false, 1.0]);

    $battle = new SwitchPokemonAction()->handle($battle, combatantIndex: 1)->fresh();
    $state  = $battle?->state;

    expect($battle?->status)->toBe(BattleStatus::ONGOING)
        ->and($state?->turn)->toBe(4)
        ->and($state?->player_side->active_index)->toBe(1)
        ->and($state?->player_side->activeCombatant()->has_entered_field)->toBeTrue()
        ->and($state?->player_side->activeCombatant()->current_hp)->toBe(218 - 46)
        ->and($state?->player_side->combatants[0]->current_hp)->toBe(218)
        ->and($state?->player_side->combatants[0]->confusion_turns_left)->toBe(0)
        ->and($state?->player_side->combatants[0]->ailment)->toBe(Ailment::PARALYSIS)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
            new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Shiny Pikachu!'),
            new BattleLogEntry(3, BattleLogKind::EVENT, 'You switched Pokémon to Eevee.'),
            new BattleLogEntry(3, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(3, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
        ]);
});

it('still deals end-of-turn damage to both sides after a switch', function () {
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'category' => MoveCategory::AILMENT, 'type' => PokemonType::NORMAL, 'power' => null]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
        'move_ids'                      => [$growl->id],
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
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'types' => [PokemonType::NORMAL], 'ailment' => Ailment::BURN, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'ailment' => Ailment::POISON]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$growl->id, true]);

    $state = new SwitchPokemonAction()->handle($battle, combatantIndex: 1)->fresh()?->state;

    expect($state?->player_side->activeCombatant()->current_hp)->toBe(218 - 13)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218 - 27)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(1, BattleLogKind::EVENT, 'You switched Pokémon to Eevee.'),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, 'Eevee is hurt by its burn!'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 13 HP damage.'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, 'The wild Pidgey is hurt by poison!'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 27 HP damage.'),
        ]);
});

it('asks for a send-out when the switched-in pokemon faints', function () {
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee        = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
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
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'types' => [PokemonType::NORMAL], 'current_hp' => 10, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'move_ids' => [$tackle->id]]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, false, 1.0]);

    $battle = new SwitchPokemonAction()->handle($battle, combatantIndex: 1)->fresh();

    expect($battle?->status)->toBe(BattleStatus::ONGOING)
        ->and($battle?->state->is_awaiting_send_out)->toBeTrue()
        ->and($battle?->state->player_side->activeCombatant()->current_hp)->toBe(0)
        ->and($battle?->state->log)->toEqual([
            new BattleLogEntry(1, BattleLogKind::EVENT, 'You switched Pokémon to Eevee.'),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 10 HP damage.'),
            new BattleLogEntry(1, BattleLogKind::EVENT, 'Eevee fainted!'),
        ]);
});

it('refuses to switch to the pokemon in battle, a fainted one or one not in the team', function (int $combatantIndex, string $expectedMessage) {
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
        'move_ids'                      => ['move-1'],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => new BattleState(
        turn: 3,
        player_side: new BattleSide(combatants: [
            new Combatant(...$combatant),
            new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'is_shiny' => true, 'current_hp' => 0]),
        ], active_index: 0),
        opponent_side: $battle->state->opponent_side,
        is_awaiting_send_out: false,
        log: [],
    )]);

    RandomHelper::fake([]);

    expect(fn () => new SwitchPokemonAction()->handle($battle, combatantIndex: $combatantIndex))
        ->toThrow(InvalidSwitchException::class, $expectedMessage)
        ->and($battle->fresh()?->state->turn)->toBe(3)
        ->and($battle->fresh()?->state->player_side->active_index)->toBe(0);
})->with([
    'in battle'       => [0, 'Pikachu is already in battle.'],
    'fainted'         => [1, "Shiny Eevee has fainted and can't battle."],
    'not in the team' => [2, "That Pokémon isn't in this battle."],
]);

it('refuses a switch when the battle is over or awaiting a send-out', function (BattleStatus $status, bool $isAwaitingSendOut) {
    $battle = Battle::factory()->create(['status' => $status]);
    $battle->update(['state' => $battle->state->withAwaitingSendOut($isAwaitingSendOut)]);

    RandomHelper::fake([]);

    expect(fn () => new SwitchPokemonAction()->handle($battle, combatantIndex: 1))
        ->toThrow(BattleNotReadyForTurnException::class)
        ->and($battle->fresh()?->state->turn)->toBe($battle->state->turn);
})->with([
    'awaiting a send-out' => [BattleStatus::ONGOING, true],
    'ended'               => [BattleStatus::WON, false],
]);
