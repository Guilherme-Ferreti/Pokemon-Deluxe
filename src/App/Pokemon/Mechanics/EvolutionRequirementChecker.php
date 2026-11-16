<?php

declare(strict_types=1);

namespace App\Pokemon\Mechanics;

use App\Pokemon\Enums\EvolutionRequirement;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokemon;
use Shared\Models\SpeciesEvolution;

final class EvolutionRequirementChecker
{
    /**
     * @param  list<string>  $ownedEvolutionItemIds
     */
    public function check(Pokemon $pokemon, SpeciesEvolution $evolution, array $ownedEvolutionItemIds): ?EvolutionRequirement
    {
        if ($evolution->gender instanceof PokemonGender && $pokemon->gender !== $evolution->gender) {
            return EvolutionRequirement::GENDER;
        }

        if ($evolution->min_level !== null && $pokemon->level < $evolution->min_level) {
            return EvolutionRequirement::LEVEL;
        }

        $item = $evolution->evolutionItem;

        if ($item instanceof EvolutionItem && ! in_array($item->id, $ownedEvolutionItemIds, true)) {
            return EvolutionRequirement::ITEM;
        }

        return null;
    }
}
