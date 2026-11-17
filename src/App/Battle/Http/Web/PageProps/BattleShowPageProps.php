<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\PageProps;

use App\Battle\Enums\BattleScene;
use App\Battle\Mechanics\DamageCalculator;
use App\Pokemon\Mechanics\ExperienceCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Pokemon;
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
     * @return array{type: BattleType, scene: BattleScene, status: BattleStatus, turn: int, log: list<array{turn: int, entries: list<array{kind: BattleLogKind, text: string, moveName: ?string}>}>, isAwaitingSendOut: bool}
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
     * @return list<array{turn: int, entries: list<array{kind: BattleLogKind, text: string, moveName: ?string}>}>
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
            fn (int $turn, array $entries) => ['turn' => $turn, 'entries' => $entries],
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
            'team'      => $this->team($side),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getMovesProp(): array
    {
        $moveIds          = $this->battle->state->player_side->activeCombatant()->move_ids;
        $moves            = Move::query()->whereKey($moveIds)->get()->keyBy('id');
        $opponentTypes    = $this->battle->state->opponent_side->activeCombatant()->types;
        $damageCalculator = new DamageCalculator;

        return array_map(fn (Move $move) => [
            'id'               => $move->id,
            'name'             => $move->name,
            'type'             => $move->type,
            'power'            => $move->power,
            'isZMove'          => $move->is_z_move,
            'isDynamaxMove'    => $move->is_dynamax_move,
            'isSuperEffective' => $move->power !== null && $damageCalculator->typeMultiplier($move->type, ...$opponentTypes) > 1,
        ], array_map($moves->findOrFail(...), $moveIds));
    }

    /**
     * @return array{experienceGains: list<array{name: string, portrait: ?string, earnedExperience: int, levelBefore: int, levelAfter: int, experienceProgress: ?int}>}|null
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

        $pokemon = Pokemon::query()
            ->whereKey(array_map(fn (ExperienceGain $gain) => $combatants[$gain->combatant_index]->pokemon_id, $gains))
            ->get()
            ->keyBy('id');

        return [
            'experienceGains' => array_map(function (ExperienceGain $gain) use ($combatants, $pokemon) {
                $combatant = $combatants[$gain->combatant_index];

                return [
                    'name'               => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
                    'portrait'           => $this->portraitOf($combatant),
                    'earnedExperience'   => $gain->earned_experience,
                    'levelBefore'        => $gain->level_before,
                    'levelAfter'         => $gain->level_after,
                    'experienceProgress' => $this->experienceProgress($combatant->pokemon_id === null ? null : $pokemon->get($combatant->pokemon_id)),
                ];
            }, $gains),
        ];
    }

    private function experienceProgress(?Pokemon $pokemon): ?int
    {
        if ($pokemon === null) {
            return null;
        }

        if ($pokemon->level >= (int) config('game.pokemon.max_level')) {
            return 100;
        }

        $experienceCalculator = new ExperienceCalculator;
        $levelStart           = $experienceCalculator->experienceForLevel($pokemon->level);
        $nextLevelStart       = $experienceCalculator->experienceForLevel($pokemon->level + 1);

        return (int) floor(($pokemon->experience - $levelStart) / ($nextLevelStart - $levelStart) * 100);
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
     * @return list<array{name: string, isFainted: bool}>
     */
    private function team(BattleSide $side): array
    {
        return array_map(fn (Combatant $combatant) => [
            'name'      => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
            'isFainted' => $combatant->isFainted(),
        ], $side->combatants);
    }

    /**
     * @return list<array{name: string, portrait: ?string, level: int, currentHp: int, maxHp: int, ailment: ?Ailment, isFainted: bool, isActive: bool}>
     */
    private function playerTeam(BattleSide $side): array
    {
        return array_map(fn (Combatant $combatant, int $index) => [
            'name'      => $this->speciesOf($combatant)->getDisplayName($combatant->is_shiny),
            'portrait'  => $this->portraitOf($combatant),
            'level'     => $combatant->level,
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
