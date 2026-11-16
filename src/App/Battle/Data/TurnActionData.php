<?php

declare(strict_types=1);

namespace App\Battle\Data;

use App\Battle\Enums\ActionBlocker;
use App\Battle\Enums\Side;
use App\Battle\Enums\TurnActionOutcome;
use App\Battle\ValueObjects\MoveOutcome;
use LogicException;
use Shared\Models\Move;
use Shared\ValueObjects\Combatant;

final class TurnActionData
{
    public ?ActionBlocker $blocker = null;

    public bool $isConfusionChecked = false;

    public ?MoveOutcome $moveOutcome = null;

    public float $typeMultiplier = 1.0;

    public ?TurnActionOutcome $outcome = null;

    public function __construct(
        public readonly TurnData $turn,
        public readonly Side $side,
        public readonly Move $move,
    ) {}

    public function defendingSide(): Side
    {
        return $this->side->other();
    }

    public function attacker(): Combatant
    {
        return $this->turn->activeCombatant($this->side);
    }

    public function defender(): Combatant
    {
        return $this->turn->activeCombatant($this->defendingSide());
    }

    public function rolledMoveOutcome(): MoveOutcome
    {
        return $this->moveOutcome ?? throw new LogicException('The move outcome must be rolled first.');
    }
}
