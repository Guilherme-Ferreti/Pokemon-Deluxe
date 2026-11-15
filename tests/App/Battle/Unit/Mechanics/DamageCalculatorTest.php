<?php

declare(strict_types=1);

use App\Battle\Mechanics\DamageCalculator;
use App\Pokemon\Mechanics\StatsCalculator;
use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonType;
use Shared\Models\Move;

describe('type multiplier', function () {
    it('returns the type multiplier of an attack against a single-type defender', function (PokemonType $defenderType, float $expectedMultiplier) {
        $multiplier = new DamageCalculator()->typeMultiplier(PokemonType::ELECTRIC, $defenderType);

        expect($multiplier)->toBe($expectedMultiplier);
    })->with([
        'super effective'    => [PokemonType::WATER, 2.0],
        'neutral'            => [PokemonType::NORMAL, 1.0],
        'not very effective' => [PokemonType::GRASS, 0.5],
        'immune'             => [PokemonType::GROUND, 0.0],
    ]);

    it('multiplies both type multipliers against a dual-type defender', function (PokemonType $secondDefenderType, float $expectedMultiplier) {
        $multiplier = new DamageCalculator()->typeMultiplier(PokemonType::ELECTRIC, PokemonType::WATER, $secondDefenderType);

        expect($multiplier)->toBe($expectedMultiplier);
    })->with([
        'both super effective'         => [PokemonType::FLYING, 4.0],
        'super and not very effective' => [PokemonType::GRASS, 1.0],
        'one immune type'              => [PokemonType::GROUND, 0.0],
    ]);
});

describe('spec 1 matchups with a neutral 80-power move and no boost', function () {
    it('lets two level 100 pokemon KO each other in 4 hits', function () {
        $level100Stats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $tackle        = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 80]);

        $damage = new DamageCalculator()->calculate($level100Stats, [PokemonType::FIRE], $level100Stats, [PokemonType::WATER], $tackle, isCriticalHit: false, randomFactor: 1.0);

        expect($damage)->toBe(114)
            ->and((int) ceil($level100Stats->hp / $damage))->toBe(4);
    });

    it('lets level 100 KO level 50 in 2 hits while level 50 needs 7', function () {
        $level100Stats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $level50Stats  = new StatsCalculator()->calculate(level: 50, boost: 0, isShiny: false);
        $tackle        = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 80]);

        $level100Damage = new DamageCalculator()->calculate($level100Stats, [PokemonType::FIRE], $level50Stats, [PokemonType::WATER], $tackle, isCriticalHit: false, randomFactor: 1.0);
        $level50Damage  = new DamageCalculator()->calculate($level50Stats, [PokemonType::WATER], $level100Stats, [PokemonType::FIRE], $tackle, isCriticalHit: false, randomFactor: 1.0);

        expect((int) ceil($level50Stats->hp / $level100Damage))->toBe(2)
            ->and((int) ceil($level100Stats->hp / $level50Damage))->toBe(7);
    });

    it('gives a +100 boosted level 50 with a STAB super effective move a real chance against level 100', function () {
        $level100Stats       = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $boostedLevel50Stats = new StatsCalculator()->calculate(level: 50, boost: 100, isShiny: false);
        $ember               = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::FIRE, 'power' => 80]);
        $tackle              = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 80]);

        $level50Damage  = new DamageCalculator()->calculate($boostedLevel50Stats, [PokemonType::FIRE], $level100Stats, [PokemonType::GRASS], $ember, isCriticalHit: false, randomFactor: 1.0);
        $level100Damage = new DamageCalculator()->calculate($level100Stats, [PokemonType::GRASS], $boostedLevel50Stats, [PokemonType::FIRE], $tackle, isCriticalHit: false, randomFactor: 1.0);

        expect((int) ceil($level100Stats->hp / $level50Damage))->toBe(2)
            ->and((int) ceil($boostedLevel50Stats->hp / $level100Damage))->toBe(4);
    });

    it('lets level 100 KO level 1 in one hit', function () {
        $level100Stats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $level1Stats   = new StatsCalculator()->calculate(level: 1, boost: 0, isShiny: false);
        $tackle        = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 80]);

        $damage = new DamageCalculator()->calculate($level100Stats, [PokemonType::FIRE], $level1Stats, [PokemonType::WATER], $tackle, isCriticalHit: false, randomFactor: 1.0);

        expect((int) ceil($level1Stats->hp / $damage))->toBe(1);
    });
});

describe('damage', function () {
    it('applies each multiplier to a level 100 hit', function (array $attackerTypes, bool $isShinyDefender, bool $isCriticalHit, float $randomFactor, int $expectedDamage) {
        $attackerStats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $defenderStats = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: $isShinyDefender);
        $ember         = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::FIRE, 'power' => 80]);

        $damage = new DamageCalculator()->calculate($attackerStats, $attackerTypes, $defenderStats, [PokemonType::NORMAL], $ember, $isCriticalHit, $randomFactor);

        expect($damage)->toBe($expectedDamage);
    })->with([
        'no multiplier'   => [[PokemonType::WATER], false, false, 1.0, 114],
        'STAB'            => [[PokemonType::FIRE], false, false, 1.0, 171],
        'critical hit'    => [[PokemonType::WATER], false, true, 1.0, 171],
        'shiny defender'  => [[PokemonType::WATER], true, false, 1.0, 85],
        'low random roll' => [[PokemonType::WATER], false, false, 0.85, 97],
        'all multipliers' => [[PokemonType::FIRE], true, true, 0.85, 163],
    ]);

    it('deals no damage to an immune defender', function () {
        $stats  = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $tackle = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::NORMAL, 'power' => 80]);

        $damage = new DamageCalculator()->calculate($stats, [PokemonType::NORMAL], $stats, [PokemonType::GHOST], $tackle, isCriticalHit: true, randomFactor: 1.0);

        expect($damage)->toBe(0);
    });

    it('deals at least 1 damage when the move is not resisted to zero', function () {
        $level1Stats      = new StatsCalculator()->calculate(level: 1, boost: 0, isShiny: false);
        $shinyLevel1Stats = new StatsCalculator()->calculate(level: 1, boost: 0, isShiny: true);
        $weakEmber        = Move::factory()->make(['category' => MoveCategory::DAMAGE, 'type' => PokemonType::FIRE, 'power' => 10]);

        $damage = new DamageCalculator()->calculate($level1Stats, [PokemonType::NORMAL], $shinyLevel1Stats, [PokemonType::WATER, PokemonType::DRAGON], $weakEmber, isCriticalHit: false, randomFactor: 0.85);

        expect($damage)->toBe(1);
    });

    it('deals no damage with a move that has no power', function () {
        $stats       = new StatsCalculator()->calculate(level: 100, boost: 0, isShiny: false);
        $thunderWave = Move::factory()->make(['category' => MoveCategory::AILMENT, 'type' => PokemonType::ELECTRIC, 'power' => null]);

        $damage = new DamageCalculator()->calculate($stats, [PokemonType::ELECTRIC], $stats, [PokemonType::WATER], $thunderWave, isCriticalHit: false, randomFactor: 1.0);

        expect($damage)->toBe(0);
    });
});
