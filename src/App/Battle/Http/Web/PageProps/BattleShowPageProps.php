<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\PageProps;

use App\Battle\Enums\BattleScene;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
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
     * @return array{type: BattleType, scene: BattleScene, status: BattleStatus, turn: int, log: list<array{turn: int, isEnd: bool, entries: list<array{kind: BattleLogKind, text: string, moveName: ?string}>}>, isAwaitingSendOut: bool}
     */
    private function getBattleProp(): array
    {
        return [
            'type'              => $this->battle->type,
            'scene'             => BattleScene::fromBattleType($this->battle->type),
            'status'            => $this->battle->status,
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
        $moveIds = $this->battle->state->player_side->activeCombatant()->move_ids;
        $moves   = Move::query()->whereKey($moveIds)->get()->keyBy('id');

        return array_map(fn (Move $move) => [
            'id'            => $move->id,
            'name'          => $move->name,
            'type'          => $move->type,
            'isZMove'       => $move->is_z_move,
            'isDynamaxMove' => $move->is_dynamax_move,
        ], array_map($moves->findOrFail(...), $moveIds));
    }

    /**
     * @return array{experienceGains: list<array{name: string, portrait: ?string, gender: PokemonGender, earnedExperience: int, levelBefore: int, levelAfter: int, isFromExperienceShare: bool}>}|null
     */
    private function getResultProp(): ?array
    {
        if ($this->battle->status === BattleStatus::ONGOING) {
            return null;
        }

        $combatants = $this->battle->state->player_side->combatants;
        $gains      = array_values(array_filter(
            $this->battle->state->experience_gains,
            fn (ExperienceGain $gain) => $gain->earned_experience > 0,
        ));

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
     * Unrevealed combatants (gym leaders and the Elite Four keep their team secret) carry no species data.
     *
     * @return list<array{name: ?string, portrait: ?string, gender: ?PokemonGender, level: ?int, boost: ?int, currentHp: ?int, maxHp: ?int, isFainted: bool, isActive: bool, isRevealed: bool}>
     */
    private function opponentTeam(BattleSide $side): array
    {
        $keepsTeamSecret = in_array($this->battle->type, [BattleType::GYM, BattleType::ELITE_FOUR], true);

        return array_map(function (Combatant $combatant, int $index) use ($side, $keepsTeamSecret) {
            $isActive = $index === $side->active_index;

            if ($keepsTeamSecret && ! $combatant->has_entered_field && ! $isActive) {
                return [
                    'name'       => null,
                    'portrait'   => null,
                    'gender'     => null,
                    'level'      => null,
                    'boost'      => null,
                    'currentHp'  => null,
                    'maxHp'      => null,
                    'isFainted'  => false,
                    'isActive'   => false,
                    'isRevealed' => false,
                ];
            }

            return [
                'name'       => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
                'portrait'   => $this->portraitOf($combatant),
                'gender'     => $combatant->gender,
                'level'      => $combatant->level,
                'boost'      => $isActive ? $combatant->boost : 0,
                'currentHp'  => $combatant->current_hp,
                'maxHp'      => $combatant->max_hp,
                'isFainted'  => $combatant->isFainted(),
                'isActive'   => $isActive,
                'isRevealed' => true,
            ];
        }, $side->combatants, array_keys($side->combatants));
    }

    /**
     * @return list<array{name: string, portrait: ?string, gender: PokemonGender, level: int, boost: int, currentHp: int, maxHp: int, ailment: ?Ailment, isFainted: bool, isActive: bool}>
     */
    private function playerTeam(BattleSide $side): array
    {
        return array_map(fn (Combatant $combatant, int $index) => [
            'name'      => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
            'portrait'  => $this->portraitOf($combatant),
            'gender'    => $combatant->gender,
            'level'     => $combatant->level,
            'boost'     => $combatant->boost,
            'currentHp' => $combatant->current_hp,
            'maxHp'     => $combatant->max_hp,
            'ailment'   => $combatant->ailment,
            'isFainted' => $combatant->isFainted(),
            'isActive'  => $index === $side->active_index,
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
}
