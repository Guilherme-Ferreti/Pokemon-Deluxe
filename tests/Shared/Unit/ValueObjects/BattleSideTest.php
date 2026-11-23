<?php

declare(strict_types=1);

use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\Combatant;

it('returns the active combatant', function () {
    $values = [
        'pokemonId'                => null,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::FIRE],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 200,
        'currentHp'                => 200,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $charmander = new Combatant(...[...$values, 'speciesId' => 'charmander']);
    $squirtle   = new Combatant(...[...$values, 'speciesId' => 'squirtle']);

    $side = new BattleSide(combatants: [$charmander, $squirtle], activeIndex: 1);

    expect($side->activeCombatant())->toBe($squirtle);
});

it('has combatants left while any of them has hp', function (array $currentHps, bool $hasCombatantsLeft) {
    $values = [
        'pokemonId'                => null,
        'speciesId'                => 'species-1',
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::FIRE],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 200,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $combatants = array_map(fn (int $currentHp) => new Combatant(...[...$values, 'currentHp' => $currentHp]), $currentHps);

    $side = new BattleSide(combatants: array_values($combatants), activeIndex: 0);

    expect($side->hasCombatantsLeft())->toBe($hasCombatantsLeft);
})->with([
    'all standing' => [[200, 50], true],
    'one left'     => [[0, 1], true],
    'all fainted'  => [[0, 0], false],
]);

it('rejects a side without combatants or with an active index out of range', function (int $combatantCount, int $activeIndex) {
    $combatant = new Combatant(
        pokemonId: null,
        speciesId: 'species-1',
        level: 50,
        boost: 0,
        isShiny: false,
        gender: PokemonGender::MALE,
        types: [PokemonType::FIRE],
        moveIds: ['move-1'],
        maxHp: 200,
        currentHp: 200,
        ailment: null,
        ailmentTurnsLeft: 0,
        confusionTurnsLeft: 0,
        hasEnteredField: true,
        usedOncePerBattleMoveIds: [],
    );

    expect(fn () => new BattleSide(combatants: array_fill(0, $combatantCount, $combatant), activeIndex: $activeIndex))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'no combatants'      => [0, 0],
    'index past the end' => [2, 2],
    'negative index'     => [2, -1],
]);
