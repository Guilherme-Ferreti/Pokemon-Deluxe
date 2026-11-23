<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Shared\Enums\Ailment;
use Shared\Enums\Area;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\ExperienceGain;

use function Pest\Laravel\travelTo;

it('saves and reloads the battle state unchanged', function () {
    $user  = User::factory()->create();
    $state = new BattleState(
        turn: 7,
        playerSide: new BattleSide(
            combatants: [
                new Combatant(
                    pokemonId: 'pokemon-1',
                    speciesId: 'species-1',
                    level: 55,
                    boost: 12,
                    isShiny: true,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::WATER, PokemonType::FLYING],
                    moveIds: ['move-1', 'move-2', 'move-3', 'move-4'],
                    maxHp: 300,
                    currentHp: 0,
                    ailment: Ailment::BURN,
                    ailmentTurnsLeft: 0,
                    confusionTurnsLeft: 0,
                    hasEnteredField: true,
                    usedOncePerBattleMoveIds: ['move-4'],
                ),
                new Combatant(
                    pokemonId: 'pokemon-2',
                    speciesId: 'species-2',
                    level: 60,
                    boost: 0,
                    isShiny: false,
                    gender: PokemonGender::GENDERLESS,
                    types: [PokemonType::STEEL],
                    moveIds: ['move-5'],
                    maxHp: 250,
                    currentHp: 120,
                    ailment: Ailment::SLEEP,
                    ailmentTurnsLeft: 2,
                    confusionTurnsLeft: 3,
                    hasEnteredField: true,
                    usedOncePerBattleMoveIds: [],
                ),
            ],
            activeIndex: 1,
        ),
        opponentSide: new BattleSide(
            combatants: [
                new Combatant(
                    pokemonId: null,
                    speciesId: 'species-3',
                    level: 58,
                    boost: 0,
                    isShiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::GRASS],
                    moveIds: ['move-6', 'move-7'],
                    maxHp: 260,
                    currentHp: 0,
                    ailment: null,
                    ailmentTurnsLeft: 0,
                    confusionTurnsLeft: 0,
                    hasEnteredField: true,
                    usedOncePerBattleMoveIds: [],
                    knockedOutBy: 1,
                ),
            ],
            activeIndex: 0,
        ),
        isAwaitingSendOut: true,
        log: [
            new BattleLogEntry(turn: 0, kind: BattleLogKind::INFO, text: 'A wild Bulbasaur appeared!'),
            new BattleLogEntry(turn: 6, kind: BattleLogKind::MOVE, text: 'Squirtle used Water Gun!', moveName: 'Water Gun'),
        ],
        experienceGains: [new ExperienceGain(combatantIndex: 1, earnedExperience: 1500, levelBefore: 60, levelAfter: 61, isFromExperienceShare: false, isAtMaxLevel: false)],
        area: Area::FOREST,
    );

    $battle = Battle::factory()->for($user)->create([
        'type'   => BattleType::TRAINER,
        'status' => BattleStatus::ONGOING,
        'state'  => $state,
    ]);

    $reloadedBattle = Battle::query()->findOrFail($battle->id);

    expect($reloadedBattle->state->equals($state))->toBeTrue()
        ->and($reloadedBattle->state)->toEqual($state)
        ->and($reloadedBattle->type)->toBe(BattleType::TRAINER)
        ->and($reloadedBattle->status)->toBe(BattleStatus::ONGOING)
        ->and($user->battles()->pluck('id')->all())->toBe([$battle->id]);
});

it('refuses to save a state that is not a battle state', function () {
    expect(fn () => Battle::factory()->create(['state' => ['turn' => 1]]))
        ->toThrow(InvalidArgumentException::class);
});

it('prunes only battles that ended more than 7 days ago', function () {
    travelTo('2026-01-10 12:00:00');

    $oldOngoingBattle  = Battle::factory()->create(['status' => BattleStatus::ONGOING, 'updated_at' => '2026-01-01 12:00:00']);
    $oldEndedBattle    = Battle::factory()->create(['status' => BattleStatus::WON, 'updated_at' => '2026-01-03 11:59:59']);
    $recentEndedBattle = Battle::factory()->create(['status' => BattleStatus::LOST, 'updated_at' => '2026-01-03 12:00:01']);

    expect(Artisan::call('model:prune', ['--model' => [Battle::class]]))->toBe(0)
        ->and(Battle::query()->pluck('id')->all())->toEqualCanonicalizing([$oldOngoingBattle->id, $recentEndedBattle->id])
        ->and(Battle::query()->whereKey($oldEndedBattle->id)->exists())->toBeFalse();
});

it('schedules model pruning daily', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())->toMatch('/0 0 \* \* \*\s+php artisan model:prune/');
});
