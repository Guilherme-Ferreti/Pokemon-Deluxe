<?php

declare(strict_types=1);

use App\Battle\Actions\SendOutPokemonAction;
use App\Battle\Exceptions\BattleNotAwaitingSendOutException;
use App\Battle\Exceptions\PokemonHasFaintedException;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Species;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

it('sends out the chosen pokemon and stops awaiting a send-out', function () {
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);

    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => new BattleState(
        turn: 3,
        player_side: new BattleSide(combatants: [
            new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: $pikachu->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                move_ids: ['move-1'],
                max_hp: 218,
                current_hp: 0,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            ),
            new Combatant(
                pokemon_id: 'pokemon-2',
                species_id: $eevee->id,
                level: 50,
                boost: 0,
                is_shiny: true,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL],
                move_ids: ['move-1'],
                max_hp: 273,
                current_hp: 273,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: false,
                used_once_per_battle_move_ids: [],
            ),
        ], active_index: 0),
        opponent_side: $battle->state->opponent_side,
        is_awaiting_send_out: true,
        log: ['Pikachu fainted!'],
    )]);

    $state = new SendOutPokemonAction()->handle($battle, combatantIndex: 1)->fresh()?->state;

    expect($state?->player_side->active_index)->toBe(1)
        ->and($state?->player_side->activeCombatant()->has_entered_field)->toBeTrue()
        ->and($state?->is_awaiting_send_out)->toBeFalse()
        ->and($state?->turn)->toBe(3)
        ->and($state?->log)->toBe(['Pikachu fainted!', 'Go! Shiny Eevee!']);
});

it('refuses a send-out when the battle is not awaiting one', function (BattleStatus $status, bool $isAwaitingSendOut) {
    $battle = Battle::factory()->create(['status' => $status]);
    $battle->update(['state' => $battle->state->withAwaitingSendOut($isAwaitingSendOut)]);

    expect(fn () => new SendOutPokemonAction()->handle($battle, combatantIndex: 0))
        ->toThrow(BattleNotAwaitingSendOutException::class);
})->with([
    'ongoing, not awaiting' => [BattleStatus::ONGOING, false],
    'ended'                 => [BattleStatus::LOST, true],
]);

it('refuses to send out a fainted pokemon or one that is not in the team', function (int $combatantIndex, string $expectedException) {
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);

    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => new BattleState(
        turn: 3,
        player_side: new BattleSide(combatants: [
            new Combatant(
                pokemon_id: 'pokemon-1',
                species_id: $pikachu->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                move_ids: ['move-1'],
                max_hp: 218,
                current_hp: 0,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: true,
                used_once_per_battle_move_ids: [],
            ),
            new Combatant(
                pokemon_id: 'pokemon-2',
                species_id: $pikachu->id,
                level: 50,
                boost: 0,
                is_shiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::ELECTRIC],
                move_ids: ['move-1'],
                max_hp: 218,
                current_hp: 218,
                ailment: null,
                ailment_turns_left: 0,
                confusion_turns_left: 0,
                has_entered_field: false,
                used_once_per_battle_move_ids: [],
            ),
        ], active_index: 0),
        opponent_side: $battle->state->opponent_side,
        is_awaiting_send_out: true,
        log: [],
    )]);

    expect(fn () => new SendOutPokemonAction()->handle($battle, combatantIndex: $combatantIndex))
        ->toThrow($expectedException)
        ->and($battle->fresh()?->state->is_awaiting_send_out)->toBeTrue();
})->with([
    'fainted'         => [0, PokemonHasFaintedException::class],
    'not in the team' => [2, InvalidArgumentException::class],
]);
