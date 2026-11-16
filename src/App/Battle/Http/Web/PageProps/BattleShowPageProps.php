<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\PageProps;

use App\Battle\Mechanics\DamageCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\Combatant;

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
        ];
    }

    /**
     * @return array{type: BattleType, turn: int, log: list<string>}
     */
    private function getBattleProp(): array
    {
        return [
            'type' => $this->battle->type,
            'turn' => $this->battle->state->turn,
            'log'  => $this->battle->state->log,
        ];
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
            'team'      => $this->team($side),
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
            'sprite'       => $species->getDisplayFrontSprite($combatant->gender, $combatant->is_shiny),
            'hpPercentage' => (int) ceil($combatant->current_hp / $combatant->max_hp * 100),
            'team'         => $this->team($side),
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
