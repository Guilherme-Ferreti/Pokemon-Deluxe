<?php

declare(strict_types=1);

use App\Battle\Mechanics\AilmentInflictionChecker;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\Combatant;

it('allows an ailment only when the target can still get it', function (Ailment $ailment, array $targetTypes, ?Ailment $currentAilment, int $confusionTurnsLeft, int $currentHp, bool $canInflict) {
    $target = new Combatant(
        pokemon_id: null,
        species_id: 'species-1',
        level: 50,
        boost: 0,
        is_shiny: false,
        gender: PokemonGender::MALE,
        types: array_values($targetTypes),
        move_ids: ['move-1'],
        max_hp: 218,
        current_hp: $currentHp,
        ailment: $currentAilment,
        ailment_turns_left: 0,
        confusion_turns_left: $confusionTurnsLeft,
        has_entered_field: true,
        used_once_per_battle_move_ids: [],
    );

    expect(new AilmentInflictionChecker()->canInflict($ailment, $target))->toBe($canInflict);
})->with([
    'burn on a healthy target'            => [Ailment::BURN, [PokemonType::NORMAL], null, 0, 218, true],
    'sleep on any type'                   => [Ailment::SLEEP, [PokemonType::PSYCHIC], null, 0, 218, true],
    'a second major ailment'              => [Ailment::BURN, [PokemonType::NORMAL], Ailment::PARALYSIS, 0, 218, false],
    'confusion on top of a major ailment' => [Ailment::CONFUSION, [PokemonType::NORMAL], Ailment::PARALYSIS, 0, 218, true],
    'confusion while already confused'    => [Ailment::CONFUSION, [PokemonType::NORMAL], null, 2, 218, false],
    'a fainted target'                    => [Ailment::POISON, [PokemonType::NORMAL], null, 0, 0, false],
    'burn on a fire type'                 => [Ailment::BURN, [PokemonType::GRASS, PokemonType::FIRE], null, 0, 218, false],
    'freeze on an ice type'               => [Ailment::FREEZE, [PokemonType::ICE], null, 0, 218, false],
    'paralysis on an electric type'       => [Ailment::PARALYSIS, [PokemonType::ELECTRIC], null, 0, 218, false],
    'poison on a poison type'             => [Ailment::POISON, [PokemonType::POISON], null, 0, 218, false],
    'poison on a steel type'              => [Ailment::POISON, [PokemonType::WATER, PokemonType::STEEL], null, 0, 218, false],
]);
