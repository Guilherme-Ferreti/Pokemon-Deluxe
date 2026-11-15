<?php

declare(strict_types=1);

use App\Battle\Mechanics\StatusEffectRoller;
use Shared\Enums\ActionBlocker;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\ValueObjects\Combatant;

describe('action blocker', function () {
    it('lets a pokemon act without rolling when it has no status effect', function () {
        $combatant = new Combatant(
            pokemon_id: null,
            species_id: 'species-1',
            level: 50,
            boost: 0,
            is_shiny: false,
            gender: PokemonGender::MALE,
            types: [PokemonType::NORMAL],
            move_ids: ['move-1'],
            max_hp: 200,
            current_hp: 200,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: true,
            used_once_per_battle_move_ids: [],
        );

        RandomHelper::fake([]);

        expect(new StatusEffectRoller()->rollActionBlocker($combatant))->toBeNull();
    });

    it('rolls the blocker of each status effect', function (?Ailment $ailment, int $ailmentTurnsLeft, int $confusionTurnsLeft, array $rolls, ?ActionBlocker $expectedBlocker) {
        $combatant = new Combatant(
            pokemon_id: null,
            species_id: 'species-1',
            level: 50,
            boost: 0,
            is_shiny: false,
            gender: PokemonGender::MALE,
            types: [PokemonType::NORMAL],
            move_ids: ['move-1'],
            max_hp: 200,
            current_hp: 200,
            ailment: $ailment,
            ailment_turns_left: $ailmentTurnsLeft,
            confusion_turns_left: $confusionTurnsLeft,
            has_entered_field: true,
            used_once_per_battle_move_ids: [],
        );

        RandomHelper::fake(array_values($rolls));

        expect(new StatusEffectRoller()->rollActionBlocker($combatant))->toBe($expectedBlocker);
    })->with([
        'stays frozen'                => [Ailment::FREEZE, 0, 0, [false], ActionBlocker::FROZEN],
        'thaws out'                   => [Ailment::FREEZE, 0, 0, [true], null],
        'asleep with turns left'      => [Ailment::SLEEP, 2, 0, [], ActionBlocker::ASLEEP],
        'wakes up with no turns left' => [Ailment::SLEEP, 0, 0, [], null],
        'hurts itself in confusion'   => [null, 0, 3, [true], ActionBlocker::HURT_ITSELF_IN_CONFUSION],
        'acts despite confusion'      => [null, 0, 3, [false], null],
        'fully paralyzed'             => [Ailment::PARALYSIS, 0, 0, [true], ActionBlocker::PARALYZED],
        'acts despite paralysis'      => [Ailment::PARALYSIS, 0, 0, [false], null],
        'confused and paralyzed'      => [Ailment::PARALYSIS, 0, 2, [false, true], ActionBlocker::PARALYZED],
        'confused after thawing'      => [Ailment::FREEZE, 0, 2, [true, true], ActionBlocker::HURT_ITSELF_IN_CONFUSION],
        'burn and poison never block' => [Ailment::BURN, 0, 0, [], null],
    ]);
});

describe('duration', function () {
    it('rolls sleep and confusion turns within their ranges', function (Ailment $ailment, int $minTurns, int $maxTurns) {
        $durations = collect(range(1, 100))->map(fn () => new StatusEffectRoller()->durationFor($ailment));

        expect($durations->min())->toBeGreaterThanOrEqual($minTurns)
            ->and($durations->max())->toBeLessThanOrEqual($maxTurns);
    })->with([
        'sleep'     => [Ailment::SLEEP, 1, 3],
        'confusion' => [Ailment::CONFUSION, 1, 4],
    ]);

    it('gives no countdown to ailments that last until the battle ends', function (Ailment $ailment) {
        RandomHelper::fake([]);

        expect(new StatusEffectRoller()->durationFor($ailment))->toBe(0);
    })->with([
        'burn'      => [Ailment::BURN],
        'poison'    => [Ailment::POISON],
        'paralysis' => [Ailment::PARALYSIS],
        'freeze'    => [Ailment::FREEZE],
    ]);
});
