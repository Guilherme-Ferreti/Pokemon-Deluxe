<?php

declare(strict_types=1);

namespace App\Battle\Data;

use App\Battle\Enums\Side;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LogicException;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

final class TurnData
{
    public BattleState $state;

    /** @var EloquentCollection<string, Move> */
    public EloquentCollection $moves;

    /** @var EloquentCollection<string, Species> */
    public EloquentCollection $species;

    public Move $opponentMove;

    /** @var list<Side> */
    public array $turnOrder = [];

    public ?TurnActionData $playerAction = null;

    public ?TurnActionData $opponentAction = null;

    /** @var list<string> */
    public private(set) array $logLines = [];

    public function __construct(
        public readonly Battle $battle,
        public readonly int $moveSlot,
    ) {
        $this->state = $battle->state;
    }

    public function log(string $line): void
    {
        $this->logLines[] = $line;
    }

    public function playerMove(): Move
    {
        $moveIds = $this->state->player_side->activeCombatant()->move_ids;

        return $this->moves->findOrFail($moveIds[$this->moveSlot - 1] ?? throw new LogicException('The move slot must be checked first.'));
    }

    public function startActionFor(Side $side): TurnActionData
    {
        if ($side === Side::PLAYER) {
            return $this->playerAction = new TurnActionData($this, $side, $this->playerMove());
        }

        return $this->opponentAction = new TurnActionData($this, $side, $this->opponentMove);
    }

    public function actionOf(Side $side): ?TurnActionData
    {
        return $side === Side::PLAYER ? $this->playerAction : $this->opponentAction;
    }

    public function activeCombatant(Side $side): Combatant
    {
        return $side === Side::PLAYER
            ? $this->state->player_side->activeCombatant()
            : $this->state->opponent_side->activeCombatant();
    }

    public function replaceActiveCombatant(Side $side, Combatant $combatant): void
    {
        $this->state = $side === Side::PLAYER
            ? $this->state->withPlayerSide($this->state->player_side->withActiveCombatant($combatant))
            : $this->state->withOpponentSide($this->state->opponent_side->withActiveCombatant($combatant));
    }

    public function damageActiveCombatant(Side $side, int $damage): void
    {
        $combatant = $this->activeCombatant($side);

        $this->replaceActiveCombatant($side, $combatant->withCurrentHp(max(0, $combatant->current_hp - $damage)));
    }

    public function logFaintIfFainted(Side $side): bool
    {
        if (! $this->activeCombatant($side)->isFainted()) {
            return false;
        }

        $this->log("{$this->displayName($side, atStart: true)} fainted!");

        return true;
    }

    public function displayName(Side $side, bool $atStart): string
    {
        $combatant = $this->activeCombatant($side);

        $name = $this->species->findOrFail($combatant->species_id)->name;
        $name = $combatant->is_shiny ? "Shiny {$name}" : $name;

        if ($side === Side::OPPONENT && $this->battle->type === BattleType::WILD) {
            return ($atStart ? 'The wild ' : 'the wild ') . $name;
        }

        return $name;
    }
}
