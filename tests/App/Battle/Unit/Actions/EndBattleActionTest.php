<?php

declare(strict_types=1);

use App\Battle\Actions\EndBattleAction;
use App\Battle\Events\BattleEnded;
use Illuminate\Support\Facades\Event;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\ExperienceGain;

it('ends the battle and dispatches BattleEnded once', function (BattleStatus $status, bool $isWin) {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['type' => BattleType::WILD, 'status' => BattleStatus::ONGOING]);

    new EndBattleAction()->handle($battle, $status);

    expect($battle->fresh()?->status)->toBe($status);

    Event::assertDispatchedTimes(BattleEnded::class, 1);
    Event::assertDispatched(BattleEnded::class, fn (BattleEnded $event) => $event->battleId === $battle->id
        && $event->userId === $battle->user_id
        && $event->type === BattleType::WILD
        && $event->status === $status
        && $event->isWin === $isWin);
})->with([
    'won'       => [BattleStatus::WON, true],
    'caught'    => [BattleStatus::CAUGHT, true],
    'lost'      => [BattleStatus::LOST, false],
    'forfeited' => [BattleStatus::FORFEITED, false],
]);

it('refuses to end a battle that has already ended', function () {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['status' => BattleStatus::WON]);

    expect(fn () => new EndBattleAction()->handle($battle, BattleStatus::LOST))
        ->toThrow(LogicException::class)
        ->and($battle->fresh()?->status)->toBe(BattleStatus::WON);

    Event::assertNotDispatched(BattleEnded::class);
});

it('refuses to end a battle as ongoing', function () {
    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    expect(fn () => new EndBattleAction()->handle($battle, BattleStatus::ONGOING))
        ->toThrow(InvalidArgumentException::class);
});

describe('experience', function () {
    it('pays every pokemon that entered the field for each defeated opponent, whatever the ending', function (BattleStatus $status, ?string $resultText) {
        Event::fake([BattleEnded::class]);

        $activePokemon   = Pokemon::factory()->create(['level' => 50]);
        $switchedPokemon = Pokemon::factory()->create(['level' => 25]);
        $benchedPokemon  = Pokemon::factory()->create(['level' => 50]);
        $pikachu         = Species::factory()->create(['name' => 'Pikachu']);

        $combatant = [
            'pokemon_id'                    => null,
            'species_id'                    => $pikachu->id,
            'level'                         => 50,
            'boost'                         => 0,
            'is_shiny'                      => false,
            'gender'                        => PokemonGender::MALE,
            'types'                         => [PokemonType::NORMAL],
            'move_ids'                      => ['move-1'],
            'max_hp'                        => 218,
            'current_hp'                    => 218,
            'ailment'                       => null,
            'ailment_turns_left'            => 0,
            'confusion_turns_left'          => 0,
            'has_entered_field'             => true,
            'used_once_per_battle_move_ids' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::TRAINER,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 5,
                player_side: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'pokemon_id' => $activePokemon->id]),
                    new Combatant(...[...$combatant, 'pokemon_id' => $switchedPokemon->id, 'level' => 25]),
                    new Combatant(...[...$combatant, 'pokemon_id' => $benchedPokemon->id, 'has_entered_field' => false]),
                ], active_index: 0),
                opponent_side: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'current_hp' => 0, 'knocked_out_by' => 0]),
                    new Combatant(...[...$combatant, 'current_hp' => 0, 'knocked_out_by' => 1]),
                    new Combatant(...$combatant),
                ], active_index: 2),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, $status);

        expect($activePokemon->fresh())->experience->toBe(50_937)->level->toBe(50)
            ->and($switchedPokemon->fresh())->experience->toBe(26_875)->level->toBe(26)
            ->and($benchedPokemon->fresh())->experience->toBe(50_000)->level->toBe(50)
            ->and($battle->fresh()?->state->experience_gains)->toEqual([
                new ExperienceGain(combatant_index: 0, earned_experience: 937, level_before: 50, level_after: 50),
                new ExperienceGain(combatant_index: 1, earned_experience: 1_875, level_before: 25, level_after: 26),
            ])
            ->and($battle->fresh()?->state->log)->toEqual([
                ...($resultText === null ? [] : [new BattleLogEntry(5, BattleLogKind::INFO, $resultText)]),
                new BattleLogEntry(5, BattleLogKind::EVENT, 'Pikachu gained 937 XP!'),
                new BattleLogEntry(5, BattleLogKind::EVENT, 'Pikachu gained 1,875 XP!'),
                new BattleLogEntry(5, BattleLogKind::EVENT, 'Pikachu grew to Lv 26!'),
            ]);
    })->with([
        'won'       => [BattleStatus::WON, 'You won!'],
        'lost'      => [BattleStatus::LOST, 'You lost...'],
        'forfeited' => [BattleStatus::FORFEITED, null],
    ]);

    it('pays nothing for running when no opponent was knocked out', function () {
        Event::fake([BattleEnded::class]);

        $pokemon = Pokemon::factory()->create(['level' => 50]);

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 2,
                player_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: $pokemon->id,
                    species_id: 'species-1',
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::NORMAL],
                    move_ids: ['move-1'],
                    max_hp: 218,
                    current_hp: 218,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(
                    pokemon_id: null,
                    species_id: 'species-2',
                    level: 50,
                    boost: 0,
                    is_shiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL],
                    move_ids: ['move-2'],
                    max_hp: 218,
                    current_hp: 100,
                    ailment: null,
                    ailment_turns_left: 0,
                    confusion_turns_left: 0,
                    has_entered_field: true,
                    used_once_per_battle_move_ids: [],
                )], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, BattleStatus::FORFEITED);

        expect($pokemon->fresh()?->experience)->toBe(50_000)
            ->and($battle->fresh()?->state->experience_gains)->toEqual([
                new ExperienceGain(combatant_index: 0, earned_experience: 0, level_before: 50, level_after: 50),
            ]);
    });

    it('pays a catch like a knockout by the active pokemon', function () {
        Event::fake([BattleEnded::class]);

        $firstPokemon  = Pokemon::factory()->create(['level' => 50]);
        $activePokemon = Pokemon::factory()->create(['level' => 50]);
        $pikachu       = Species::factory()->create(['name' => 'Pikachu']);

        $combatant = [
            'pokemon_id'                    => null,
            'species_id'                    => $pikachu->id,
            'level'                         => 50,
            'boost'                         => 0,
            'is_shiny'                      => false,
            'gender'                        => PokemonGender::MALE,
            'types'                         => [PokemonType::NORMAL],
            'move_ids'                      => ['move-1'],
            'max_hp'                        => 218,
            'current_hp'                    => 218,
            'ailment'                       => null,
            'ailment_turns_left'            => 0,
            'confusion_turns_left'          => 0,
            'has_entered_field'             => true,
            'used_once_per_battle_move_ids' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 4,
                player_side: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'pokemon_id' => $firstPokemon->id]),
                    new Combatant(...[...$combatant, 'pokemon_id' => $activePokemon->id]),
                ], active_index: 1),
                opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'current_hp' => 50])], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, BattleStatus::CAUGHT);

        expect($battle->fresh()?->state->experience_gains)->toEqual([
            new ExperienceGain(combatant_index: 0, earned_experience: 250, level_before: 50, level_after: 50),
            new ExperienceGain(combatant_index: 1, earned_experience: 375, level_before: 50, level_after: 50),
        ]);
    });

    it('counts Battle Tower XP without paying it', function () {
        Event::fake([BattleEnded::class]);

        $pokemon = Pokemon::factory()->create(['level' => 50]);

        $combatant = [
            'pokemon_id'                    => null,
            'species_id'                    => 'species-1',
            'level'                         => 50,
            'boost'                         => 0,
            'is_shiny'                      => false,
            'gender'                        => PokemonGender::MALE,
            'types'                         => [PokemonType::NORMAL],
            'move_ids'                      => ['move-1'],
            'max_hp'                        => 218,
            'current_hp'                    => 218,
            'ailment'                       => null,
            'ailment_turns_left'            => 0,
            'confusion_turns_left'          => 0,
            'has_entered_field'             => true,
            'used_once_per_battle_move_ids' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::BATTLE_TOWER,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 3,
                player_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => $pokemon->id])], active_index: 0),
                opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'current_hp' => 0, 'knocked_out_by' => 0])], active_index: 0),
                is_awaiting_send_out: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, BattleStatus::WON);

        expect($pokemon->fresh()?->experience)->toBe(50_000)
            ->and($battle->fresh()?->state->experience_gains)->toEqual([
                new ExperienceGain(combatant_index: 0, earned_experience: 375, level_before: 50, level_after: 50),
            ]);
    });
});
