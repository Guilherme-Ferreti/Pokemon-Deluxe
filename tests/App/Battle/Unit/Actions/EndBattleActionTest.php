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
            'pokemonId'                => null,
            'speciesId'                => $pikachu->id,
            'level'                    => 50,
            'boost'                    => 0,
            'isShiny'                  => false,
            'gender'                   => PokemonGender::MALE,
            'types'                    => [PokemonType::NORMAL],
            'moveIds'                  => ['move-1'],
            'maxHp'                    => 218,
            'currentHp'                => 218,
            'ailment'                  => null,
            'ailmentTurnsLeft'         => 0,
            'confusionTurnsLeft'       => 0,
            'hasEnteredField'          => true,
            'usedOncePerBattleMoveIds' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::TRAINER,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 5,
                playerSide: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'pokemonId' => $activePokemon->id]),
                    new Combatant(...[...$combatant, 'pokemonId' => $switchedPokemon->id, 'level' => 25]),
                    new Combatant(...[...$combatant, 'pokemonId' => $benchedPokemon->id, 'hasEnteredField' => false]),
                ], activeIndex: 0),
                opponentSide: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'currentHp' => 0, 'knockedOutBy' => 0]),
                    new Combatant(...[...$combatant, 'currentHp' => 0, 'knockedOutBy' => 1]),
                    new Combatant(...$combatant),
                ], activeIndex: 2),
                isAwaitingSendOut: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, $status);

        expect($activePokemon->fresh())->experience->toBe(50_937)->level->toBe(50)
            ->and($switchedPokemon->fresh())->experience->toBe(26_875)->level->toBe(26)
            ->and($benchedPokemon->fresh())->experience->toBe(50_000)->level->toBe(50)
            ->and($battle->fresh()?->state->experienceGains)->toEqual([
                new ExperienceGain(combatantIndex: 0, earnedExperience: 937, levelBefore: 50, levelAfter: 50, isFromExperienceShare: false, isAtMaxLevel: false),
                new ExperienceGain(combatantIndex: 1, earnedExperience: 1_875, levelBefore: 25, levelAfter: 26, isFromExperienceShare: false, isAtMaxLevel: false),
            ])
            ->and($battle->fresh()?->state->log)->toEqual([
                ...($resultText === null ? [] : [new BattleLogEntry(5, BattleLogKind::RESULT, $resultText)]),
                new BattleLogEntry(5, BattleLogKind::EVENT, 'Pikachu gained 937 XP!'),
                new BattleLogEntry(5, BattleLogKind::EVENT, 'Pikachu gained 1,875 XP and grew to level 26!'),
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
                playerSide: new BattleSide(combatants: [new Combatant(
                    pokemonId: $pokemon->id,
                    speciesId: 'species-1',
                    level: 50,
                    boost: 0,
                    isShiny: false,
                    gender: PokemonGender::MALE,
                    types: [PokemonType::NORMAL],
                    moveIds: ['move-1'],
                    maxHp: 218,
                    currentHp: 218,
                    ailment: null,
                    ailmentTurnsLeft: 0,
                    confusionTurnsLeft: 0,
                    hasEnteredField: true,
                    usedOncePerBattleMoveIds: [],
                )], activeIndex: 0),
                opponentSide: new BattleSide(combatants: [new Combatant(
                    pokemonId: null,
                    speciesId: 'species-2',
                    level: 50,
                    boost: 0,
                    isShiny: false,
                    gender: PokemonGender::FEMALE,
                    types: [PokemonType::NORMAL],
                    moveIds: ['move-2'],
                    maxHp: 218,
                    currentHp: 100,
                    ailment: null,
                    ailmentTurnsLeft: 0,
                    confusionTurnsLeft: 0,
                    hasEnteredField: true,
                    usedOncePerBattleMoveIds: [],
                )], activeIndex: 0),
                isAwaitingSendOut: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, BattleStatus::FORFEITED);

        expect($pokemon->fresh()?->experience)->toBe(50_000)
            ->and($battle->fresh()?->state->experienceGains)->toEqual([
                new ExperienceGain(combatantIndex: 0, earnedExperience: 0, levelBefore: 50, levelAfter: 50, isFromExperienceShare: false, isAtMaxLevel: false),
            ]);
    });

    it('marks a max level pokemon that earned XP as at the max level and logs it', function (BattleStatus $status, int $opponentHp, bool $isAtMaxLevel, array $experienceLog) {
        Event::fake([BattleEnded::class]);

        $pokemon = Pokemon::factory()->create(['level' => 100]);
        $zoroark = Species::factory()->create(['name' => 'Zoroark']);

        $combatant = [
            'pokemonId'                => $pokemon->id,
            'speciesId'                => $zoroark->id,
            'level'                    => 100,
            'boost'                    => 0,
            'isShiny'                  => false,
            'gender'                   => PokemonGender::MALE,
            'types'                    => [PokemonType::DARK],
            'moveIds'                  => ['move-1'],
            'maxHp'                    => 400,
            'currentHp'                => 400,
            'ailment'                  => null,
            'ailmentTurnsLeft'         => 0,
            'confusionTurnsLeft'       => 0,
            'hasEnteredField'          => true,
            'usedOncePerBattleMoveIds' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 3,
                playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
                opponentSide: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'pokemonId' => null, 'currentHp' => $opponentHp, 'knockedOutBy' => $opponentHp === 0 ? 0 : null]),
                ], activeIndex: 0),
                isAwaitingSendOut: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, $status);

        expect($pokemon->fresh())->experience->toBe(100_000)->level->toBe(100)
            ->and($battle->fresh()?->state->experienceGains)->toEqual([
                new ExperienceGain(combatantIndex: 0, earnedExperience: 0, levelBefore: 100, levelAfter: 100, isFromExperienceShare: false, isAtMaxLevel: $isAtMaxLevel),
            ])
            ->and($battle->fresh()?->state->log)->toEqual(array_map(
                fn (string $text) => new BattleLogEntry(3, $text === 'You won!' ? BattleLogKind::RESULT : BattleLogKind::EVENT, $text),
                $experienceLog,
            ));
    })->with([
        'won'                  => [BattleStatus::WON, 0, true, ['You won!', 'Zoroark is at the max level and gained no XP.']],
        'ran with no knockout' => [BattleStatus::FORFEITED, 400, false, []],
    ]);

    it('pays a catch like a knockout by the active pokemon', function () {
        Event::fake([BattleEnded::class]);

        $firstPokemon  = Pokemon::factory()->create(['level' => 50]);
        $activePokemon = Pokemon::factory()->create(['level' => 50]);
        $pikachu       = Species::factory()->create(['name' => 'Pikachu']);

        $combatant = [
            'pokemonId'                => null,
            'speciesId'                => $pikachu->id,
            'level'                    => 50,
            'boost'                    => 0,
            'isShiny'                  => false,
            'gender'                   => PokemonGender::MALE,
            'types'                    => [PokemonType::NORMAL],
            'moveIds'                  => ['move-1'],
            'maxHp'                    => 218,
            'currentHp'                => 218,
            'ailment'                  => null,
            'ailmentTurnsLeft'         => 0,
            'confusionTurnsLeft'       => 0,
            'hasEnteredField'          => true,
            'usedOncePerBattleMoveIds' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::WILD,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 4,
                playerSide: new BattleSide(combatants: [
                    new Combatant(...[...$combatant, 'pokemonId' => $firstPokemon->id]),
                    new Combatant(...[...$combatant, 'pokemonId' => $activePokemon->id]),
                ], activeIndex: 1),
                opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'currentHp' => 50])], activeIndex: 0),
                isAwaitingSendOut: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, BattleStatus::CAUGHT);

        expect($battle->fresh()?->state->experienceGains)->toEqual([
            new ExperienceGain(combatantIndex: 0, earnedExperience: 250, levelBefore: 50, levelAfter: 50, isFromExperienceShare: false, isAtMaxLevel: false),
            new ExperienceGain(combatantIndex: 1, earnedExperience: 375, levelBefore: 50, levelAfter: 50, isFromExperienceShare: false, isAtMaxLevel: false),
        ]);
    });

    it('counts Battle Tower XP without paying it', function () {
        Event::fake([BattleEnded::class]);

        $pokemon = Pokemon::factory()->create(['level' => 50]);

        $combatant = [
            'pokemonId'                => null,
            'speciesId'                => 'species-1',
            'level'                    => 50,
            'boost'                    => 0,
            'isShiny'                  => false,
            'gender'                   => PokemonGender::MALE,
            'types'                    => [PokemonType::NORMAL],
            'moveIds'                  => ['move-1'],
            'maxHp'                    => 218,
            'currentHp'                => 218,
            'ailment'                  => null,
            'ailmentTurnsLeft'         => 0,
            'confusionTurnsLeft'       => 0,
            'hasEnteredField'          => true,
            'usedOncePerBattleMoveIds' => [],
        ];

        $battle = Battle::factory()->create([
            'type'   => BattleType::BATTLE_TOWER,
            'status' => BattleStatus::ONGOING,
            'state'  => new BattleState(
                turn: 3,
                playerSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => $pokemon->id])], activeIndex: 0),
                opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'currentHp' => 0, 'knockedOutBy' => 0])], activeIndex: 0),
                isAwaitingSendOut: false,
                log: [],
            ),
        ]);

        new EndBattleAction()->handle($battle, BattleStatus::WON);

        expect($pokemon->fresh()?->experience)->toBe(50_000)
            ->and($battle->fresh()?->state->experienceGains)->toEqual([
                new ExperienceGain(combatantIndex: 0, earnedExperience: 375, levelBefore: 50, levelAfter: 50, isFromExperienceShare: false, isAtMaxLevel: false),
            ]);
    });
});
