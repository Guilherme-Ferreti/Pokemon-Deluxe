<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Shared\Enums\Ailment;
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
        player_side: new BattleSide(
            combatants: [
                new Combatant(
                    pokemon_id: 'pokemon-1',
                    species_id: 'species-1',
                    level: 55,
                    boost: 12,
                    is_shiny: true,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::WATER, PokemonType::FLYING],
                    move_ids: ['move-1', 'move-2', 'move-3', 'move-4'],
                    max_hp: 300,
                    current_hp: 0,
                    ailment: Ailment::BURN,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: ['move-4'],
                ),
                new Combatant(
                    pokemon_id: 'pokemon-2',
                    species_id: 'species-2',
                    level: 60,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::GENDERLESS,
                    types: [PokemonType::STEEL],
                    move_ids: ['move-5'],
                    max_hp: 250,
                    current_hp: 120,
                    ailment: Ailment::SLEEP,
                    ailment_turns_left: 2,
                    confusion_turns_left: 3,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                ),
            ],
            active_index: 1,
        ),
        opponent_side: new BattleSide(
            combatants: [
                new Combatant(
                    pokemon_id: null,
                    species_id: 'species-3',
                    level: 58,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::GRASS],
                    move_ids: ['move-6', 'move-7'],
                    max_hp: 260,
                    current_hp: 0,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                    knocked_out_by: 1,
                ),
            ],
            active_index: 0,
        ),
        is_awaiting_send_out: true,
        log: [
            new BattleLogEntry(turn: 0, kind: BattleLogKind::INFO, text: 'A wild Bulbasaur appeared!'),
            new BattleLogEntry(turn: 6, kind: BattleLogKind::MOVE, text: 'Squirtle used Water Gun!', move_name: 'Water Gun'),
        ],
        experience_gains: [new ExperienceGain(combatant_index: 1, earned_experience: 1500, level_before: 60, level_after: 61, is_from_experience_share: false, is_at_max_level: false)],
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
