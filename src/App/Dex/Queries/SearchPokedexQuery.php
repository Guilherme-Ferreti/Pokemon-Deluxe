<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use App\Dex\Enums\SearchPokedexSortBy;
use App\Dex\Filters\SearchPokedexFilters;
use Illuminate\Database\Eloquent\Builder;
use Shared\Enums\Generation;
use Shared\Enums\PokemonType;
use Shared\Enums\SortDirection;
use Shared\Enums\SpeciesTrait;
use Shared\Models\Species;

final class SearchPokedexQuery
{
    /**
     * @return Builder<Species>
     */
    public function handle(SearchPokedexFilters $filters): Builder
    {
        return Species::query()
            ->select('id', 'slug', 'name', 'types', 'pokedex_number', 'portraits', 'generation')
            ->when($filters->search !== null, fn (Builder $query) => $this->applySearch($query, $filters->search))
            ->when($filters->types !== [], fn (Builder $query) => $this->applyTypes($query, $filters->types))
            ->when($filters->traits !== [], fn (Builder $query) => $this->applyTraits($query, $filters->traits))
            ->when($filters->generations !== [], fn (Builder $query) => $this->applyGenerations($query, $filters->generations))
            ->when(true, fn (Builder $query) => $this->applySorting($query, $filters->sort, $filters->dir));
    }

    /**
     * @param  Builder<Species>  $query
     * @return Builder<Species>
     */
    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->whereLike('name', '%' . $search . '%');
    }

    /**
     * @param  Builder<Species>  $query
     * @param  list<PokemonType>  $types
     * @return Builder<Species>
     */
    private function applyTypes(Builder $query, array $types): Builder
    {
        foreach ($types as $type) {
            $query->whereJsonContains('types', $type->value);
        }

        return $query;
    }

    /**
     * @param  Builder<Species>  $query
     * @param  list<SpeciesTrait>  $traits
     * @return Builder<Species>
     */
    private function applyTraits(Builder $query, array $traits): Builder
    {
        return $query
            ->when(in_array(SpeciesTrait::BABY, $traits, true), fn (Builder $query) => $query->where('is_baby', true))
            ->when(in_array(SpeciesTrait::LEGENDARY, $traits, true), fn (Builder $query) => $query->where('is_legendary', true))
            ->when(in_array(SpeciesTrait::MYTHICAL, $traits, true), fn (Builder $query) => $query->where('is_mythical', true))
            ->when(in_array(SpeciesTrait::MEGA, $traits, true), fn (Builder $query) => $query->where('is_mega', true))
            ->when(in_array(SpeciesTrait::GMAX, $traits, true), fn (Builder $query) => $query->where('is_gmax', true))
            ->when(in_array(SpeciesTrait::ULTRA_BEAST, $traits, true), fn (Builder $query) => $query->where('is_ultra_beast', true));
    }

    /**
     * @param  Builder<Species>  $query
     * @param  list<Generation>  $generations
     * @return Builder<Species>
     */
    private function applyGenerations(Builder $query, array $generations): Builder
    {
        return $query->whereIn('generation', $generations);
    }

    /**
     * @param  Builder<Species>  $query
     * @return Builder<Species>
     */
    private function applySorting(Builder $query, SearchPokedexSortBy $sort, SortDirection $dir): Builder
    {
        return match ($sort) {
            SearchPokedexSortBy::POKEDEX_NUMBER => $query->orderBy('pokedex_order'),
            SearchPokedexSortBy::NAME           => $query->orderBy('name', $dir->value),
        };
    }
}
