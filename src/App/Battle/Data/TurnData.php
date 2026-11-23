<?php

declare(strict_types=1);

namespace App\Battle\Data;

use App\Battle\Enums\Side;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LogicException;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\BattleLogEntry;
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

    /** @var list<BattleLogEntry> */
    public private(set) array $logEntries = [];

    public function __construct(
        public readonly Battle $battle,
        public readonly ?int $moveSlot = null,
        public readonly ?int $switchIndex = null,
    ) {
        $this->state = $battle->state;
    }

    public function log(BattleLogKind $kind, string $text, ?string $moveName = null): void
    {
        $this->logEntries[] = new BattleLogEntry($this->state->turn, $kind, $text, $moveName);
    }

    public function logDamage(int $hpLost): void
    {
        $this->log(BattleLogKind::DAMAGE, sprintf('It dealt %s HP damage.', number_format($hpLost)));
    }

    public function playerMove(): Move
    {
        if ($this->moveSlot === null) {
            throw new LogicException('The player is not using a move this turn.');
        }

        $moveIds = $this->state->playerSide->activeCombatant()->moveIds;

        return $this->moves->findOrFail($moveIds[$this->moveSlot - 1] ?? throw new LogicException('The move slot must be checked first.'));
    }

    public function isUsingMove(Side $side): bool
    {
        return $side === Side::OPPONENT || $this->moveSlot !== null;
    }

    public function chosenSwitchIndex(): int
    {
        return $this->switchIndex ?? throw new LogicException('The player is not switching this turn.');
    }

    public function switchPlayerActiveIndex(int $combatantIndex): void
    {
        $this->state = $this->state->withPlayerSide($this->state->playerSide->withActiveIndex($combatantIndex));
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
            ? $this->state->playerSide->activeCombatant()
            : $this->state->opponentSide->activeCombatant();
    }

    public function replaceActiveCombatant(Side $side, Combatant $combatant): void
    {
        $this->state = $side === Side::PLAYER
            ? $this->state->withPlayerSide($this->state->playerSide->withActiveCombatant($combatant))
            : $this->state->withOpponentSide($this->state->opponentSide->withActiveCombatant($combatant));
    }

    public function damageActiveCombatant(Side $side, int $damage): int
    {
        $combatant = $this->activeCombatant($side);
        $hpLost    = min($damage, $combatant->currentHp);

        $this->replaceActiveCombatant($side, $combatant->withCurrentHp($combatant->currentHp - $hpLost));

        return $hpLost;
    }

    public function logFaintIfFainted(Side $side): bool
    {
        if (! $this->activeCombatant($side)->isFainted()) {
            return false;
        }

        $this->log(BattleLogKind::EVENT, "{$this->displayName($side, atStart: true)} fainted!");

        return true;
    }

    public function displayName(Side $side, bool $atStart): string
    {
        $combatant = $this->activeCombatant($side);

        $name = $this->species->findOrFail($combatant->speciesId)->name;
        $name = $combatant->isShiny ? "Shiny {$name}" : $name;

        if ($side === Side::OPPONENT && $this->battle->type === BattleType::WILD) {
            return ($atStart ? 'The wild ' : 'the wild ') . $name;
        }

        return $name;
    }
}
