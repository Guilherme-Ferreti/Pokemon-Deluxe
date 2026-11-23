<?php

declare(strict_types=1);

use App\Battle\Actions\SendOutPokemonAction;
use App\Battle\Exceptions\BattleNotAwaitingSendOutException;
use App\Battle\Exceptions\PokemonHasFaintedException;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Species;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

it('sends out the chosen pokemon and stops awaiting a send-out', function () {
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);

    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => new BattleState(
        turn: 3,
        playerSide: new BattleSide(combatants: [
            new Combatant(
                pokemonId: 'pokemon-1',
                speciesId: $pikachu->id,
                level: 50,
                boost: 0,
                isShiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                moveIds: ['move-1'],
                maxHp: 218,
                currentHp: 0,
                ailment: null,
                ailmentTurnsLeft: 0,
                confusionTurnsLeft: 0,
                hasEnteredField: true,
                usedOncePerBattleMoveIds: [],
            ),
            new Combatant(
                pokemonId: 'pokemon-2',
                speciesId: $eevee->id,
                level: 50,
                boost: 0,
                isShiny: true,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::NORMAL],
                moveIds: ['move-1'],
                maxHp: 273,
                currentHp: 273,
                ailment: null,
                ailmentTurnsLeft: 0,
                confusionTurnsLeft: 0,
                hasEnteredField: false,
                usedOncePerBattleMoveIds: [],
            ),
        ], activeIndex: 0),
        opponentSide: $battle->state->opponentSide,
        isAwaitingSendOut: true,
        log: [new BattleLogEntry(2, BattleLogKind::EVENT, 'Pikachu fainted!')],
    )]);

    $state = new SendOutPokemonAction()->handle($battle, combatantIndex: 1)->fresh()?->state;

    expect($state?->playerSide->activeIndex)->toBe(1)
        ->and($state?->playerSide->activeCombatant()->hasEnteredField)->toBeTrue()
        ->and($state?->isAwaitingSendOut)->toBeFalse()
        ->and($state?->turn)->toBe(3)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(2, BattleLogKind::EVENT, 'Pikachu fainted!'),
            new BattleLogEntry(2, BattleLogKind::EVENT, 'Go! Shiny Eevee!'),
        ]);
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
        playerSide: new BattleSide(combatants: [
            new Combatant(
                pokemonId: 'pokemon-1',
                speciesId: $pikachu->id,
                level: 50,
                boost: 0,
                isShiny: false,
                gender: PokemonGender::MALE,
                types: [PokemonType::ELECTRIC],
                moveIds: ['move-1'],
                maxHp: 218,
                currentHp: 0,
                ailment: null,
                ailmentTurnsLeft: 0,
                confusionTurnsLeft: 0,
                hasEnteredField: true,
                usedOncePerBattleMoveIds: [],
            ),
            new Combatant(
                pokemonId: 'pokemon-2',
                speciesId: $pikachu->id,
                level: 50,
                boost: 0,
                isShiny: false,
                gender: PokemonGender::FEMALE,
                types: [PokemonType::ELECTRIC],
                moveIds: ['move-1'],
                maxHp: 218,
                currentHp: 218,
                ailment: null,
                ailmentTurnsLeft: 0,
                confusionTurnsLeft: 0,
                hasEnteredField: false,
                usedOncePerBattleMoveIds: [],
            ),
        ], activeIndex: 0),
        opponentSide: $battle->state->opponentSide,
        isAwaitingSendOut: true,
        log: [],
    )]);

    expect(fn () => new SendOutPokemonAction()->handle($battle, combatantIndex: $combatantIndex))
        ->toThrow($expectedException)
        ->and($battle->fresh()?->state->isAwaitingSendOut)->toBeTrue();
})->with([
    'fainted'         => [0, PokemonHasFaintedException::class],
    'not in the team' => [2, InvalidArgumentException::class],
]);
