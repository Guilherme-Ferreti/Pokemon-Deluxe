<?php

declare(strict_types=1);

use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\Combatant;

it('returns the active combatant', function () {
    $values = [
        'pokemon_id'                    => null,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::FIRE],
        'move_ids'                      => ['move-1'],
        'max_hp'                        => 200,
        'current_hp'                    => 200,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    $charmander = new Combatant(...[...$values, 'species_id' => 'charmander']);
    $squirtle   = new Combatant(...[...$values, 'species_id' => 'squirtle']);

    $side = new BattleSide(combatants: [$charmander, $squirtle], active_index: 1);

    expect($side->activeCombatant())->toBe($squirtle);
});

it('has combatants left while any of them has hp', function (array $currentHps, bool $hasCombatantsLeft) {
    $values = [
        'pokemon_id'                    => null,
        'species_id'                    => 'species-1',
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::FIRE],
        'move_ids'                      => ['move-1'],
        'max_hp'                        => 200,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    $combatants = array_map(fn (int $currentHp) => new Combatant(...[...$values, 'current_hp' => $currentHp]), $currentHps);

    $side = new BattleSide(combatants: array_values($combatants), active_index: 0);

    expect($side->hasCombatantsLeft())->toBe($hasCombatantsLeft);
})->with([
    'all standing' => [[200, 50], true],
    'one left'     => [[0, 1], true],
    'all fainted'  => [[0, 0], false],
]);

it('rejects a side without combatants or with an active index out of range', function (int $combatantCount, int $activeIndex) {
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
        current_hp: 200,
        ailment: null,
        ailment_turns_left: 0,
        confusion_turns_left: 0,
        has_entered_field: true,
        used_once_per_battle_move_ids: [],
    );

    expect(fn () => new BattleSide(combatants: array_fill(0, $combatantCount, $combatant), active_index: $activeIndex))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'no combatants'      => [0, 0],
    'index past the end' => [2, 2],
    'negative index'     => [2, -1],
]);
