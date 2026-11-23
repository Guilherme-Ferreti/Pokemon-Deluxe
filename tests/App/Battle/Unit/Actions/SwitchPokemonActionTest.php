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
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => true,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => [$thunderShock->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 3,
            playerSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'ailment' => Ailment::PARALYSIS, 'confusionTurnsLeft' => 2]),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'isShiny' => false, 'types' => [PokemonType::NORMAL], 'moveIds' => [$tackle->id], 'hasEnteredField' => false]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'isShiny' => false, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'moveIds' => [$tackle->id]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
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
        ->and($state?->playerSide->activeIndex)->toBe(1)
        ->and($state?->playerSide->activeCombatant()->hasEnteredField)->toBeTrue()
        ->and($state?->playerSide->activeCombatant()->currentHp)->toBe(218 - 46)
        ->and($state?->playerSide->combatants[0]->currentHp)->toBe(218)
        ->and($state?->playerSide->combatants[0]->confusionTurnsLeft)->toBe(0)
        ->and($state?->playerSide->combatants[0]->ailment)->toBe(Ailment::PARALYSIS)
        ->and($state?->opponentSide->activeCombatant()->currentHp)->toBe(218)
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
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => [$growl->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'types' => [PokemonType::NORMAL], 'ailment' => Ailment::BURN, 'hasEnteredField' => false]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'ailment' => Ailment::POISON]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$growl->id, true]);

    $state = new SwitchPokemonAction()->handle($battle, combatantIndex: 1)->fresh()?->state;

    expect($state?->playerSide->activeCombatant()->currentHp)->toBe(218 - 13)
        ->and($state?->opponentSide->activeCombatant()->currentHp)->toBe(218 - 27)
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
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => [$thunderShock->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'types' => [PokemonType::NORMAL], 'currentHp' => 10, 'hasEnteredField' => false]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'moveIds' => [$tackle->id]]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, false, 1.0]);

    $battle = new SwitchPokemonAction()->handle($battle, combatantIndex: 1)->fresh();

    expect($battle?->status)->toBe(BattleStatus::ONGOING)
        ->and($battle?->state->isAwaitingSendOut)->toBeTrue()
        ->and($battle?->state->playerSide->activeCombatant()->currentHp)->toBe(0)
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
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::ELECTRIC],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => new BattleState(
        turn: 3,
        playerSide: new BattleSide(combatants: [
            new Combatant(...$combatant),
            new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'isShiny' => true, 'currentHp' => 0]),
        ], activeIndex: 0),
        opponentSide: $battle->state->opponentSide,
        isAwaitingSendOut: false,
        log: [],
    )]);

    RandomHelper::fake([]);

    expect(fn () => new SwitchPokemonAction()->handle($battle, combatantIndex: $combatantIndex))
        ->toThrow(InvalidSwitchException::class, $expectedMessage)
        ->and($battle->fresh()?->state->turn)->toBe(3)
        ->and($battle->fresh()?->state->playerSide->activeIndex)->toBe(0);
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
