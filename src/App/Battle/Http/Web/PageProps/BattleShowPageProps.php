<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\PageProps;

use App\Battle\Enums\BattleScene;
use App\Pokemon\Mechanics\StatsCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\Ailment;
use Shared\Enums\Area;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\ExperienceGain;

final class BattleShowPageProps implements ProvidesInertiaProperties
{
    /** @var EloquentCollection<string, Species>|null */
    private ?EloquentCollection $species = null;

    /** @var EloquentCollection<string, Move>|null */
    private ?EloquentCollection $moves = null;

    public function __construct(
        private readonly Battle $battle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'battle'   => $this->getBattleProp(...),
            'player'   => $this->getPlayerProp(...),
            'opponent' => $this->getOpponentProp(...),
            'moves'    => $this->getMovesProp(...),
            'result'   => $this->getResultProp(...),
        ];
    }

    /**
     * @return array{type: BattleType, scene: BattleScene, status: BattleStatus, area: ?Area, turn: int, log: list<array{turn: int, isEnd: bool, entries: list<array{kind: BattleLogKind, text: string, moveName: ?string}>}>, isAwaitingSendOut: bool}
     */
    private function getBattleProp(): array
    {
        return [
            'type'              => $this->battle->type,
            'scene'             => BattleScene::fromBattleType($this->battle->type),
            'status'            => $this->battle->status,
            'area'              => $this->battle->state->area,
            'turn'              => $this->battle->state->turn,
            'log'               => $this->logByTurn(),
            'isAwaitingSendOut' => $this->battle->status === BattleStatus::ONGOING && $this->battle->state->isAwaitingSendOut,
        ];
    }

    /**
     * @return list<array{turn: int, isEnd: bool, entries: list<array{kind: BattleLogKind, text: string, moveName: ?string}>}>
     */
    private function logByTurn(): array
    {
        $entriesByTurn = [];

        foreach ($this->battle->state->log as $entry) {
            $entriesByTurn[$entry->turn][] = [
                'kind'     => $entry->kind,
                'text'     => $entry->text,
                'moveName' => $entry->moveName,
            ];
        }

        krsort($entriesByTurn);

        return array_map(
            fn (int $turn, array $entries) => [
                'turn'    => $turn,
                'isEnd'   => $this->battle->status !== BattleStatus::ONGOING && $turn === $this->battle->state->endTurn(),
                'entries' => $entries,
            ],
            array_keys($entriesByTurn),
            $entriesByTurn,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getPlayerProp(): array
    {
        $side      = $this->battle->state->playerSide;
        $combatant = $side->activeCombatant();
        $species   = $this->speciesOf($combatant);

        return [
            ...$this->combatantDetails($combatant, $species),
            'sprite'    => $species->getDisplayBackSprite($combatant->gender, $combatant->isShiny),
            'currentHp' => $combatant->currentHp,
            'maxHp'     => $combatant->maxHp,
            'team'      => $this->playerTeam($side),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getOpponentProp(): array
    {
        $side      = $this->battle->state->opponentSide;
        $combatant = $side->activeCombatant();
        $species   = $this->speciesOf($combatant);

        return [
            ...$this->combatantDetails($combatant, $species),
            'sprite'    => $species->getDisplayFrontSprite($combatant->gender, $combatant->isShiny),
            'currentHp' => $combatant->currentHp,
            'maxHp'     => $combatant->maxHp,
            'team'      => $this->opponentTeam($side),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getMovesProp(): array
    {
        return array_map(fn (Move $move) => [
            'id'            => $move->id,
            'name'          => $move->name,
            'type'          => $move->type,
            'isZMove'       => $move->is_z_move,
            'isDynamaxMove' => $move->is_dynamax_move,
        ], $this->existingMovesOf($this->battle->state->playerSide->activeCombatant()));
    }

    /**
     * @return array{experienceGains: list<array{name: string, portrait: ?string, gender: PokemonGender, earnedExperience: int, levelBefore: int, levelAfter: int, isFromExperienceShare: bool, isAtMaxLevel: bool}>}|null
     */
    private function getResultProp(): ?array
    {
        if ($this->battle->status === BattleStatus::ONGOING) {
            return null;
        }

        $combatants = $this->battle->state->playerSide->combatants;
        $gains      = array_filter(
            $this->battle->state->experienceGains,
            fn (ExperienceGain $gain) => $gain->earnedExperience > 0 || $gain->isAtMaxLevel,
        ) |> array_values(...);

        return [
            'experienceGains' => array_map(function (ExperienceGain $gain) use ($combatants) {
                $combatant = $combatants[$gain->combatantIndex];

                return [
                    'name'                  => $this->speciesOf($combatant)->getDisplayName($combatant->isShiny),
                    'portrait'              => $this->portraitOf($combatant),
                    'gender'                => $combatant->gender,
                    'earnedExperience'      => $gain->earnedExperience,
                    'levelBefore'           => $gain->levelBefore,
                    'levelAfter'            => $gain->levelAfter,
                    'isFromExperienceShare' => $gain->isFromExperienceShare,
                    'isAtMaxLevel'          => $gain->isAtMaxLevel,
                ];
            }, $gains),
        ];
    }

    private function portraitOf(Combatant $combatant): ?string
    {
        $portraits = $this->speciesOf($combatant)->portraits;

        return $combatant->isShiny ? $portraits->shiny : $portraits->regular;
    }

    /**
     * @return array<string, mixed>
     */
    private function combatantDetails(Combatant $combatant, Species $species): array
    {
        return [
            'name'    => $species->getDisplayName($combatant->isShiny),
            'gender'  => $combatant->gender,
            'level'   => $combatant->level,
            'boost'   => $combatant->boost,
            'isShiny' => $combatant->isShiny,
            'ailment' => $combatant->ailment,
        ];
    }

    /**
     * @return list<array{name: ?string, portrait: ?string, gender: ?PokemonGender, level: ?int, boost: ?int, isShiny: ?bool, statBonuses: ?array{hp: int|float, attack: int|float, damageTaken: int|float}, types: ?list<PokemonType>, moves: ?list<array{name: string, type: PokemonType, power: ?int}>, currentHp: ?int, maxHp: ?int, isFainted: bool, isActive: bool, isRevealed: bool}>
     */
    private function opponentTeam(BattleSide $side): array
    {
        return array_map(function (Combatant $combatant, int $index) use ($side) {
            $isActive = $index === $side->activeIndex;

            if (! $this->isRevealedOpponent($combatant, $index)) {
                return [
                    'name'        => null,
                    'portrait'    => null,
                    'gender'      => null,
                    'level'       => null,
                    'boost'       => null,
                    'isShiny'     => null,
                    'statBonuses' => null,
                    'types'       => null,
                    'moves'       => null,
                    'currentHp'   => null,
                    'maxHp'       => null,
                    'isFainted'   => false,
                    'isActive'    => false,
                    'isRevealed'  => false,
                ];
            }

            return [
                'name'        => $this->speciesOf($combatant)->getDisplayName($combatant->isShiny),
                'portrait'    => $this->portraitOf($combatant),
                'gender'      => $combatant->gender,
                'level'       => $combatant->level,
                'boost'       => $combatant->boost,
                'isShiny'     => $combatant->isShiny,
                'statBonuses' => $this->statBonusesOf($combatant),
                'types'       => $combatant->types,
                'moves'       => $this->moveDetailsOf($combatant),
                'currentHp'   => $combatant->currentHp,
                'maxHp'       => $combatant->maxHp,
                'isFainted'   => $combatant->isFainted(),
                'isActive'    => $isActive,
                'isRevealed'  => true,
            ];
        }, $side->combatants, array_keys($side->combatants));
    }

    /**
     * @return list<array{name: string, portrait: ?string, gender: PokemonGender, level: int, boost: int, isShiny: bool, statBonuses: array{hp: int|float, attack: int|float, damageTaken: int|float}, types: list<PokemonType>, moves: list<array{name: string, type: PokemonType, power: ?int}>, currentHp: int, maxHp: int, ailment: ?Ailment, isFainted: bool, isActive: bool}>
     */
    private function playerTeam(BattleSide $side): array
    {
        return array_map(fn (Combatant $combatant, int $index) => [
            'name'        => $this->speciesOf($combatant)->getDisplayName($combatant->isShiny),
            'portrait'    => $this->portraitOf($combatant),
            'gender'      => $combatant->gender,
            'level'       => $combatant->level,
            'boost'       => $combatant->boost,
            'isShiny'     => $combatant->isShiny,
            'statBonuses' => $this->statBonusesOf($combatant),
            'types'       => $combatant->types,
            'moves'       => $this->moveDetailsOf($combatant),
            'currentHp'   => $combatant->currentHp,
            'maxHp'       => $combatant->maxHp,
            'ailment'     => $combatant->ailment,
            'isFainted'   => $combatant->isFainted(),
            'isActive'    => $index === $side->activeIndex,
        ], $side->combatants, array_keys($side->combatants));
    }

    private function speciesOf(Combatant $combatant): Species
    {
        $this->species ??= Species::query()
            ->whereKey(array_map(
                fn (Combatant $combatant) => $combatant->speciesId,
                [...$this->battle->state->playerSide->combatants, ...$this->battle->state->opponentSide->combatants],
            ))
            ->get()
            ->keyBy('id');

        return $this->species->findOrFail($combatant->speciesId);
    }

    private function isRevealedOpponent(Combatant $combatant, int $index): bool
    {
        $keepsTeamSecret = in_array($this->battle->type, [BattleType::GYM, BattleType::ELITE_FOUR], true);

        return ! $keepsTeamSecret
            || $combatant->hasEnteredField
            || $index === $this->battle->state->opponentSide->activeIndex;
    }

    /**
     * @return array{hp: int|float, attack: int|float, damageTaken: int|float}
     */
    private function statBonusesOf(Combatant $combatant): array
    {
        $statsCalculator = new StatsCalculator;

        return [
            'hp'          => round($statsCalculator->hpBonusPercentage($combatant->boost, $combatant->isShiny), 1) |> toIntIfWhole(...),
            'attack'      => round($statsCalculator->damageDoneBonusPercentage($combatant->boost, $combatant->isShiny), 1) |> toIntIfWhole(...),
            'damageTaken' => round($statsCalculator->damageTakenChangePercentage($combatant->boost, $combatant->isShiny), 1) |> toIntIfWhole(...),
        ];
    }

    /**
     * @return list<array{name: string, type: PokemonType, power: ?int}>
     */
    private function moveDetailsOf(Combatant $combatant): array
    {
        return array_map(fn (Move $move) => [
            'name'  => $move->name,
            'type'  => $move->type,
            'power' => $move->power,
        ], $this->existingMovesOf($combatant));
    }

    /**
     * @return list<Move>
     */
    private function existingMovesOf(Combatant $combatant): array
    {
        return array_map($this->shownMoves()->get(...), $combatant->moveIds)
            |> array_filter(...)
            |> array_values(...);
    }

    /**
     * @return EloquentCollection<string, Move>
     */
    private function shownMoves(): EloquentCollection
    {
        if ($this->moves !== null) {
            return $this->moves;
        }

        $revealedOpponents = array_filter(
            $this->battle->state->opponentSide->combatants,
            $this->isRevealedOpponent(...),
            ARRAY_FILTER_USE_BOTH,
        );

        return $this->moves = Move::query()
            ->whereKey(array_merge(...array_map(
                fn (Combatant $combatant) => $combatant->moveIds,
                [...$this->battle->state->playerSide->combatants, ...array_values($revealedOpponents)],
            )))
            ->get()
            ->keyBy('id');
    }
}
