<?php

declare(strict_types=1);

namespace App\Pokemon\Queries;

use App\Pokemon\Enums\SearchBoxSortBy;
use App\Pokemon\Filters\SearchBoxFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Shared\Enums\Generation;
use Shared\Enums\PokemonTrait;
use Shared\Enums\PokemonType;
use Shared\Enums\SortDirection;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class SearchBoxQuery
{
    /**
     * @return Builder<Pokemon>
     */
    public function handle(User $user, SearchBoxFilters $filters): Builder
    {
        return $this->buildBaseQuery($user)
            ->when(true, fn (Builder $query) => $this->joinSpeciesTableIfNeeded($query, $filters))
            ->when($filters->search !== null, fn (Builder $query) => $this->applySearch($query, $filters->search))
            ->when($filters->types->isNotEmpty(), fn (Builder $query) => $this->applyTypes($query, $filters->types))
            ->when($filters->traits->isNotEmpty(), fn (Builder $query) => $this->applyTraits($query, $filters->traits))
            ->when($filters->generations->isNotEmpty(), fn (Builder $query) => $this->applyGenerations($query, $filters->generations))
            ->when(true, fn (Builder $query) => $this->applySorting($query, $filters->sort, $filters->dir));
    }

    /**
     * @return Builder<Pokemon>
     */
    private function buildBaseQuery(User $user): Builder
    {
        return Pokemon::query()
            ->whereBelongsTo($user)
            ->inBox()
            ->with('species:id,slug,name,types,sprites', 'moves:id,name')
            ->select('pokemon.id', 'pokemon.is_shiny', 'pokemon.gender', 'pokemon.level', 'pokemon.experience', 'pokemon.species_id');
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @return Builder<Pokemon>
     */
    private function joinSpeciesTableIfNeeded(Builder $query, SearchBoxFilters $filters): Builder
    {
        $shouldJoin = $filters->search !== null
            || $filters->types->isNotEmpty()
            || $filters->traits->isNotEmpty()
            || $filters->generations->isNotEmpty()
            || $filters->sort === SearchBoxSortBy::POKEDEX_NUMBER;

        return $query->when($shouldJoin, fn (Builder $query) => $query->join('species', 'species.id', '=', 'pokemon.species_id'));
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @return Builder<Pokemon>
     */
    private function applySearch(Builder $query, string $search): Builder
    {
        return $query
            ->whereLike('species.name', '%' . $search . '%');
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @param  Collection<int, PokemonType>  $types
     * @return Builder<Pokemon>
     */
    private function applyTypes(Builder $query, Collection $types): Builder
    {
        foreach ($types as $type) {
            $query->whereJsonContains('species.types', $type->value);
        }

        return $query;
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @param  Collection<int, PokemonTrait>  $traits
     * @return Builder<Pokemon>
     */
    private function applyTraits(Builder $query, Collection $traits): Builder
    {
        return $query
            ->when($traits->contains(PokemonTrait::SHINY), fn (Builder $query) => $query->where('pokemon.is_shiny', true))
            ->when($traits->contains(PokemonTrait::BABY), fn (Builder $query) => $query->where('species.is_baby', true))
            ->when($traits->contains(PokemonTrait::LEGENDARY), fn (Builder $query) => $query->where('species.is_legendary', true))
            ->when($traits->contains(PokemonTrait::MYTHICAL), fn (Builder $query) => $query->where('species.is_mythical', true))
            ->when($traits->contains(PokemonTrait::MEGA), fn (Builder $query) => $query->where('species.is_mega', true))
            ->when($traits->contains(PokemonTrait::GMAX), fn (Builder $query) => $query->where('species.is_gmax', true));
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @param  Collection<int, Generation>  $generations
     * @return Builder<Pokemon>
     */
    private function applyGenerations(Builder $query, Collection $generations): Builder
    {
        return $query->whereIn('species.generation', $generations);
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @return Builder<Pokemon>
     */
    private function applySorting(Builder $query, SearchBoxSortBy $sort, SortDirection $dir): Builder
    {
        $column = match ($sort) {
            SearchBoxSortBy::CATCH_DATE     => 'pokemon.caught_at',
            SearchBoxSortBy::EXPERIENCE     => 'pokemon.experience',
            SearchBoxSortBy::LEVEL          => 'pokemon.level',
            SearchBoxSortBy::POKEDEX_NUMBER => 'species.pokedex_order',
        };

        return $query
            ->orderBy($column, $dir->value)
            ->orderBy('pokemon.id');
    }
}
