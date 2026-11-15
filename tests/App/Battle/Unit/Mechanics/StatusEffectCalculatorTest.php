<?php

declare(strict_types=1);

use App\Battle\Mechanics\StatusEffectCalculator;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\Combatant;

it('deals end-of-turn damage from burn and poison', function (?Ailment $ailment, int $maxHp, int $currentHp, int $expectedDamage) {
    $combatant = new Combatant(
        pokemon_id: null,
        species_id: 'species-1',
        level: 50,
        boost: 0,
        is_shiny: false,
        gender: PokemonGender::MALE,
        types: [PokemonType::NORMAL],
        move_ids: ['move-1'],
        max_hp: $maxHp,
        current_hp: $currentHp,
        ailment: $ailment,
        ailment_turns_left: 0,
        confusion_turns_left: 0,
        has_entered_field: true,
        used_once_per_battle_move_ids: [],
    );

    expect(new StatusEffectCalculator()->endOfTurnDamage($combatant))->toBe($expectedDamage);
})->with([
    'burn takes 1/16 of max hp'    => [Ailment::BURN, 400, 400, 25],
    'poison takes 1/8 of max hp'   => [Ailment::POISON, 400, 100, 50],
    'at least 1 damage'            => [Ailment::BURN, 10, 10, 1],
    'no ailment'                   => [null, 400, 400, 0],
    'paralysis deals no damage'    => [Ailment::PARALYSIS, 400, 400, 0],
    'a fainted pokemon takes none' => [Ailment::POISON, 400, 0, 0],
]);
