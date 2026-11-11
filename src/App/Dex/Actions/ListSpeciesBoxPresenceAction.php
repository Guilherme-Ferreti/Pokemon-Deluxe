<?php

declare(strict_types=1);

namespace App\Dex\Actions;

use Illuminate\Support\Collection;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class ListSpeciesBoxPresenceAction
{
    /**
     * @param  Collection<int, string>  $speciesIds
     * @return Collection<int, array{speciesId: string, regularInBox: bool, shinyInBox: bool}>
     */
    public function handle(User $user, Collection $speciesIds): Collection
    {
        $pokemonList = Pokemon::query()
            ->whereBelongsTo($user)
            ->whereIn('species_id', $speciesIds)
            ->distinct()
            ->get(['species_id', 'is_shiny']);

        $regularPokemon = $pokemonList
            ->where('is_shiny', false)
            ->pluck('species_id')
            ->flip();

        $shinyPokemon = $pokemonList
            ->where('is_shiny', true)
            ->pluck('species_id')
            ->flip();

        return $speciesIds->map(fn ($speciesId) => [
            'speciesId'    => $speciesId,
            'regularInBox' => $regularPokemon->has($speciesId),
            'shinyInBox'   => $shinyPokemon->has($speciesId),
        ]);
    }
}
