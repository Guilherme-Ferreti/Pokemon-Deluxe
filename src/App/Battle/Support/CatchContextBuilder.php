<?php

declare(strict_types=1);

namespace App\Battle\Support;

use App\Battle\ValueObjects\CatchContext;
use Shared\Models\Battle;
use Shared\Models\Species;

final class CatchContextBuilder
{
    public function build(Battle $battle): CatchContext
    {
        $wildCombatant   = $battle->state->opponentSide->activeCombatant();
        $activeCombatant = $battle->state->playerSide->activeCombatant();

        return new CatchContext(
            turn: $battle->state->turn,
            area: $battle->state->area,
            isNight: $this->isNight(),
            wildSpecies: Species::query()->with('evolutions.evolutionItem')->findOrFail($wildCombatant->speciesId),
            wildLevel: $wildCombatant->level,
            wildGender: $wildCombatant->gender,
            wildAilment: $wildCombatant->ailment,
            activeSpecies: Species::query()->findOrFail($activeCombatant->speciesId),
            activeLevel: $activeCombatant->level,
            activeGender: $activeCombatant->gender,
            playerOwnsSpecies: $battle->user->pokemon()->where('species_id', $wildCombatant->speciesId)->exists(),
        );
    }

    private function isNight(): bool
    {
        $hour = now()->hour;

        return $hour >= (int) config('game.catching.night_starts_at') || $hour < (int) config('game.catching.night_ends_at');
    }
}
