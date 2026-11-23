<?php

declare(strict_types=1);

use App\Battle\Mechanics\StatusEffectCalculator;
use App\Pokemon\Mechanics\StatsCalculator;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\Combatant;

it('deals end-of-turn damage from burn and poison', function (?Ailment $ailment, int $maxHp, int $currentHp, int $expectedDamage) {
    $combatant = new Combatant(
        pokemonId: null,
        speciesId: 'species-1',
        level: 50,
        boost: 0,
        isShiny: false,
        gender: PokemonGender::MALE,
        types: [PokemonType::NORMAL],
        moveIds: ['move-1'],
        maxHp: $maxHp,
        currentHp: $currentHp,
        ailment: $ailment,
        ailmentTurnsLeft: 0,
        confusionTurnsLeft: 0,
        hasEnteredField: true,
        usedOncePerBattleMoveIds: [],
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

it('deals a 40-power typeless hit to a pokemon that hurts itself in confusion', function (bool $isShiny, int $expectedDamage) {
    $stats = new StatsCalculator()->calculate(level: 50, boost: 0, isShiny: $isShiny);

    expect(new StatusEffectCalculator()->confusionSelfHitDamage($stats))->toBe($expectedDamage);
})->with([
    'regular' => [false, 31],
    'shiny'   => [true, 29],
]);
