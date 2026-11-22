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
            'isAwaitingSendOut' => $this->battle->status === BattleStatus::ONGOING && $this->battle->state->is_awaiting_send_out,
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
                'moveName' => $entry->move_name,
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
        $side      = $this->battle->state->player_side;
        $combatant = $side->activeCombatant();
        $species   = $this->speciesOf($combatant);

        return [
            ...$this->combatantDetails($combatant, $species),
            'sprite'    => $species->getDisplayBackSprite($combatant->gender, $combatant->is_shiny),
            'currentHp' => $combatant->current_hp,
            'maxHp'     => $combatant->max_hp,
            'team'      => $this->playerTeam($side),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getOpponentProp(): array
    {
        $side      = $this->battle->state->opponent_side;
        $combatant = $side->activeCombatant();
        $species   = $this->speciesOf($combatant);

        return [
            ...$this->combatantDetails($combatant, $species),
            'sprite'    => $species->getDisplayFrontSprite($combatant->gender, $combatant->is_shiny),
            'currentHp' => $combatant->current_hp,
            'maxHp'     => $combatant->max_hp,
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
        ], $this->existingMovesOf($this->battle->state->player_side->activeCombatant()));
    }

    /**
     * @return array{experienceGains: list<array{name: string, portrait: ?string, gender: PokemonGender, earnedExperience: int, levelBefore: int, levelAfter: int, isFromExperienceShare: bool, isAtMaxLevel: bool}>}|null
     */
    private function getResultProp(): ?array
    {
        if ($this->battle->status === BattleStatus::ONGOING) {
            return null;
        }

        $combatants = $this->battle->state->player_side->combatants;
        $gains      = array_filter(
            $this->battle->state->experience_gains,
            fn (ExperienceGain $gain) => $gain->earned_experience > 0 || $gain->is_at_max_level,
        ) |> array_values(...);

        return [
            'experienceGains' => array_map(function (ExperienceGain $gain) use ($combatants) {
                $combatant = $combatants[$gain->combatant_index];

                return [
                    'name'                  => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
                    'portrait'              => $this->portraitOf($combatant),
                    'gender'                => $combatant->gender,
                    'earnedExperience'      => $gain->earned_experience,
                    'levelBefore'           => $gain->level_before,
                    'levelAfter'            => $gain->level_after,
                    'isFromExperienceShare' => $gain->is_from_experience_share,
                    'isAtMaxLevel'          => $gain->is_at_max_level,
                ];
            }, $gains),
        ];
    }

    private function portraitOf(Combatant $combatant): ?string
    {
        $portraits = $this->speciesOf($combatant)->portraits;

        return $combatant->is_shiny ? $portraits->shiny : $portraits->regular;
    }

    /**
     * @return array<string, mixed>
     */
    private function combatantDetails(Combatant $combatant, Species $species): array
    {
        return [
            'name'    => $species->getDisplayName($combatant->is_shiny),
            'gender'  => $combatant->gender,
            'level'   => $combatant->level,
            'boost'   => $combatant->boost,
            'isShiny' => $combatant->is_shiny,
            'ailment' => $combatant->ailment,
        ];
    }

    /**
     * @return list<array{name: ?string, portrait: ?string, gender: ?PokemonGender, level: ?int, boost: ?int, isShiny: ?bool, statBonuses: ?array{hp: int|float, attack: int|float, damageTaken: int|float}, types: ?list<PokemonType>, moves: ?list<array{name: string, type: PokemonType, power: ?int}>, currentHp: ?int, maxHp: ?int, isFainted: bool, isActive: bool, isRevealed: bool}>
     */
    private function opponentTeam(BattleSide $side): array
    {
        return array_map(function (Combatant $combatant, int $index) use ($side) {
            $isActive = $index === $side->active_index;

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
                'name'        => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
                'portrait'    => $this->portraitOf($combatant),
                'gender'      => $combatant->gender,
                'level'       => $combatant->level,
                'boost'       => $combatant->boost,
                'isShiny'     => $combatant->is_shiny,
                'statBonuses' => $this->statBonusesOf($combatant),
                'types'       => $combatant->types,
                'moves'       => $this->moveDetailsOf($combatant),
                'currentHp'   => $combatant->current_hp,
                'maxHp'       => $combatant->max_hp,
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
            'name'        => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
            'portrait'    => $this->portraitOf($combatant),
            'gender'      => $combatant->gender,
            'level'       => $combatant->level,
            'boost'       => $combatant->boost,
            'isShiny'     => $combatant->is_shiny,
            'statBonuses' => $this->statBonusesOf($combatant),
            'types'       => $combatant->types,
            'moves'       => $this->moveDetailsOf($combatant),
            'currentHp'   => $combatant->current_hp,
            'maxHp'       => $combatant->max_hp,
            'ailment'     => $combatant->ailment,
            'isFainted'   => $combatant->isFainted(),
            'isActive'    => $index === $side->active_index,
        ], $side->combatants, array_keys($side->combatants));
    }

    private function speciesOf(Combatant $combatant): Species
    {
        $this->species ??= Species::query()
            ->whereKey(array_map(
                fn (Combatant $combatant) => $combatant->species_id,
                [...$this->battle->state->player_side->combatants, ...$this->battle->state->opponent_side->combatants],
            ))
            ->get()
            ->keyBy('id');

        return $this->species->findOrFail($combatant->species_id);
    }

    private function isRevealedOpponent(Combatant $combatant, int $index): bool
    {
        $keepsTeamSecret = in_array($this->battle->type, [BattleType::GYM, BattleType::ELITE_FOUR], true);

        return ! $keepsTeamSecret
            || $combatant->has_entered_field
            || $index === $this->battle->state->opponent_side->active_index;
    }

    /**
     * @return array{hp: int|float, attack: int|float, damageTaken: int|float}
     */
    private function statBonusesOf(Combatant $combatant): array
    {
        $statsCalculator = new StatsCalculator;

        return [
            'hp'          => round($statsCalculator->hpBonusPercentage($combatant->boost, $combatant->is_shiny), 1) |> toIntIfWhole(...),
            'attack'      => round($statsCalculator->damageDoneBonusPercentage($combatant->boost, $combatant->is_shiny), 1) |> toIntIfWhole(...),
            'damageTaken' => round($statsCalculator->damageTakenChangePercentage($combatant->boost, $combatant->is_shiny), 1) |> toIntIfWhole(...),
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
        return array_map($this->shownMoves()->get(...), $combatant->move_ids)
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
            $this->battle->state->opponent_side->combatants,
            $this->isRevealedOpponent(...),
            ARRAY_FILTER_USE_BOTH,
        );

        return $this->moves = Move::query()
            ->whereKey(array_merge(...array_map(
                fn (Combatant $combatant) => $combatant->move_ids,
                [...$this->battle->state->player_side->combatants, ...array_values($revealedOpponents)],
            )))
            ->get()
            ->keyBy('id');
    }
}
