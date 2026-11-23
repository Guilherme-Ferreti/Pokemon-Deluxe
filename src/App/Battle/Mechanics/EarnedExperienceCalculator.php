<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use App\Pokemon\Mechanics\ExperienceCalculator;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

final class EarnedExperienceCalculator
{
    /**
     * @return array<int, int>
     */
    public function earnedByCombatant(BattleState $state, BattleType $battleType, BattleStatus $status): array
    {
        $earnedByCombatant = [];

        foreach ($state->playerSide->combatants as $combatantIndex => $combatant) {
            if (! $combatant->hasEnteredField) {
                continue;
            }

            $earnedByCombatant[$combatantIndex] = $this->earnedBy($combatantIndex, $combatant, $state, $battleType, $status);
        }

        return $earnedByCombatant;
    }

    private function earnedBy(int $combatantIndex, Combatant $combatant, BattleState $state, BattleType $battleType, BattleStatus $status): int
    {
        $earned = 0;

        foreach ($state->opponentSide->combatants as $opponentIndex => $opponent) {
            $isCaught = $status === BattleStatus::CAUGHT && $opponentIndex === $state->opponentSide->activeIndex;

            if (! $opponent->isFainted() && ! $isCaught) {
                continue;
            }

            $knockedOutBy = $isCaught ? $state->playerSide->activeIndex : $opponent->knockedOutBy;

            $earned += new ExperienceCalculator()->experienceForDefeating(
                opponentLevel: $opponent->level,
                pokemonLevel: $combatant->level,
                battleType: $battleType,
                isKnockout: $knockedOutBy === $combatantIndex,
            );
        }

        return $earned;
    }
}
