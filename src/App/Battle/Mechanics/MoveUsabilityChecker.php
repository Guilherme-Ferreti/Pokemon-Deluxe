<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\Combatant;

final class MoveUsabilityChecker
{
    public function isUsable(Move $move, Combatant $combatant, Species $species): bool
    {
        return ! $this->isOncePerBattleMoveAlreadyUsed($move, $combatant) && $this->isDynamaxMoveAllowed($move, $species);
    }

    public function isOncePerBattle(Move $move): bool
    {
        return $move->is_z_move || $move->is_dynamax_move;
    }

    public function isOncePerBattleMoveAlreadyUsed(Move $move, Combatant $combatant): bool
    {
        return $this->isOncePerBattle($move) && in_array($move->id, $combatant->used_once_per_battle_move_ids, true);
    }

    public function isDynamaxMoveAllowed(Move $move, Species $species): bool
    {
        return ! $move->is_dynamax_move || $species->is_gmax;
    }
}
