<?php

declare(strict_types=1);

use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\Combatant;

it('is fainted only at 0 hp', function (int $currentHp, bool $isFainted) {
    $combatant = new Combatant(
        pokemon_id: null,
        species_id: 'species-1',
        level: 50,
        boost: 0,
        is_shiny: false,
        gender: PokemonGender::MALE,
        types: [PokemonType::FIRE],
        move_ids: ['move-1'],
        max_hp: 200,
        current_hp: $currentHp,
        ailment: null,
        ailment_turns_left: 0,
        confusion_turns_left: 0,
        has_entered_field: true,
        used_once_per_battle_move_ids: [],
    );

    expect($combatant->isFainted())->toBe($isFainted);
})->with([
    '0 hp'    => [0, true],
    '1 hp'    => [1, false],
    'full hp' => [200, false],
]);

it('rejects invalid values', function (string $field, mixed $invalidValue) {
    $values = [
        'pokemon_id'                    => null,
        'species_id'                    => 'species-1',
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::FIRE],
        'move_ids'                      => ['move-1', 'move-2'],
        'max_hp'                        => 200,
        'current_hp'                    => 200,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
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
    'no moves'                      => ['move_ids', []],
    'five moves'                    => ['move_ids', ['move-1', 'move-2', 'move-3', 'move-4', 'move-5']],
    'max hp 0'                      => ['max_hp', 0],
    'current hp above max hp'       => ['current_hp', 201],
    'negative current hp'           => ['current_hp', -1],
    'confusion as ailment'          => ['ailment', Ailment::CONFUSION],
    'unknown ailment'               => ['ailment', Ailment::UNKNOWN],
    'negative ailment turns left'   => ['ailment_turns_left', -1],
    'negative confusion turns left' => ['confusion_turns_left', -1],
    'used move it does not have'    => ['used_once_per_battle_move_ids', ['move-9']],
    'negative knocked out by'       => ['knocked_out_by', -1],
]);
