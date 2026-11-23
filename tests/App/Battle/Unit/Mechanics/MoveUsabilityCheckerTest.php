<?php

declare(strict_types=1);

use App\Battle\Mechanics\MoveUsabilityChecker;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\Combatant;

it('decides whether a combatant can still use a move', function (bool $isZMove, bool $isDynamaxMove, bool $isUsed, bool $isGmax, bool $isOncePerBattleMoveAlreadyUsed, bool $isDynamaxMoveAllowed, bool $isUsable) {
    $move    = Move::factory()->plainDamage()->make(['id' => 'move-1', 'is_z_move' => $isZMove, 'is_dynamax_move' => $isDynamaxMove]);
    $species = Species::factory()->make(['is_gmax' => $isGmax]);

    $combatant = new Combatant(
        pokemonId: null,
        speciesId: 'species-1',
        level: 50,
        boost: 0,
        isShiny: false,
        gender: PokemonGender::MALE,
        types: [PokemonType::NORMAL],
        moveIds: ['move-1'],
        maxHp: 218,
        currentHp: 218,
        ailment: null,
        ailmentTurnsLeft: 0,
        confusionTurnsLeft: 0,
        hasEnteredField: true,
        usedOncePerBattleMoveIds: $isUsed ? ['move-1'] : [],
    );

    $checker = new MoveUsabilityChecker;

    expect($checker->isOncePerBattleMoveAlreadyUsed($move, $combatant))->toBe($isOncePerBattleMoveAlreadyUsed)
        ->and($checker->isDynamaxMoveAllowed($move, $species))->toBe($isDynamaxMoveAllowed)
        ->and($checker->isUsable($move, $combatant, $species))->toBe($isUsable);
})->with([
    'a regular move'                          => [false, false, false, false, false, true, true],
    'an unused Z-Move'                        => [true, false, false, false, false, true, true],
    'a used Z-Move'                           => [true, false, true, false, true, true, false],
    'a Dynamax move for a G-Max species'      => [false, true, false, true, false, true, true],
    'a Dynamax move for a regular species'    => [false, true, false, false, false, false, false],
    'a used Dynamax move for a G-Max species' => [false, true, true, true, true, true, false],
]);

it('treats Z-Moves and Dynamax moves as once per battle', function (bool $isZMove, bool $isDynamaxMove, bool $isOncePerBattle) {
    $move = Move::factory()->plainDamage()->make(['is_z_move' => $isZMove, 'is_dynamax_move' => $isDynamaxMove]);

    expect(new MoveUsabilityChecker()->isOncePerBattle($move))->toBe($isOncePerBattle);
})->with([
    'regular move' => [false, false, false],
    'Z-Move'       => [true, false, true],
    'Dynamax move' => [false, true, true],
]);
