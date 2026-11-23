<?php

declare(strict_types=1);

use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\Combatant;

it('is fainted only at 0 hp', function (int $currentHp, bool $isFainted) {
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
        currentHp: $currentHp,
        ailment: null,
        ailmentTurnsLeft: 0,
        confusionTurnsLeft: 0,
        hasEnteredField: true,
        usedOncePerBattleMoveIds: [],
    );

    expect($combatant->isFainted())->toBe($isFainted);
})->with([
    '0 hp'    => [0, true],
    '1 hp'    => [1, false],
    'full hp' => [200, false],
]);

it('rejects invalid values', function (string $field, mixed $invalidValue) {
    $values = [
        'pokemonId'                => null,
        'speciesId'                => 'species-1',
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::FIRE],
        'moveIds'                  => ['move-1', 'move-2'],
        'maxHp'                    => 200,
        'currentHp'                => 200,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    expect(fn () => new Combatant(...[...$values, $field => $invalidValue]))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'level 0'                       => ['level', 0],
    'level above max'               => ['level', 101],
    'negative boost'                => ['boost', -1],
    'boost above max'               => ['boost', 101],
    'no types'                      => ['types', []],
    'three types'                   => ['types', [PokemonType::FIRE, PokemonType::WATER, PokemonType::GRASS]],
    'no moves'                      => ['moveIds', []],
    'five moves'                    => ['moveIds', ['move-1', 'move-2', 'move-3', 'move-4', 'move-5']],
    'max hp 0'                      => ['maxHp', 0],
    'current hp above max hp'       => ['currentHp', 201],
    'negative current hp'           => ['currentHp', -1],
    'confusion as ailment'          => ['ailment', Ailment::CONFUSION],
    'unknown ailment'               => ['ailment', Ailment::UNKNOWN],
    'negative ailment turns left'   => ['ailmentTurnsLeft', -1],
    'negative confusion turns left' => ['confusionTurnsLeft', -1],
    'used move it does not have'    => ['usedOncePerBattleMoveIds', ['move-9']],
    'negative knocked out by'       => ['knockedOutBy', -1],
]);
