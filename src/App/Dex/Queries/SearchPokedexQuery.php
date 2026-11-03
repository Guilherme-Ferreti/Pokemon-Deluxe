<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use App\Dex\Enums\SearchPokedexSortBy;
use App\Dex\Filters\SearchPokedexFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
            ->when($filters->types->isNotEmpty(), fn (Builder $query) => $this->applyTypes($query, $filters->types))
            ->when($filters->traits->isNotEmpty(), fn (Builder $query) => $this->applyTraits($query, $filters->traits))
            ->when($filters->generations->isNotEmpty(), fn (Builder $query) => $this->applyGenerations($query, $filters->generations))
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
     * @param  Collection<int, PokemonType>  $types
     * @return Builder<Species>
     */
    private function applyTypes(Builder $query, Collection $types): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->when(
                $types->count() === 1,
                fn (Builder $query) => $query->whereJsonContains('types', $types[0]->value)
            )
            ->when(
                $types->count() === 2,
                fn (Builder $query) => $query->whereJsonContains('types', $types[0]->value)->whereJsonContains('types', $types[1]->value)
            )
        );
    }

    /**
     * @param  Builder<Species>  $query
     * @param  Collection<int, SpeciesTrait>  $traits
     * @return Builder<Species>
     */
    private function applyTraits(Builder $query, Collection $traits): Builder
    {
        return $query
            ->when($traits->contains(SpeciesTrait::BABY), fn (Builder $query) => $query->where('is_baby', true))
            ->when($traits->contains(SpeciesTrait::LEGENDARY), fn (Builder $query) => $query->where('is_legendary', true))
            ->when($traits->contains(SpeciesTrait::MYTHICAL), fn (Builder $query) => $query->where('is_mythical', true))
            ->when($traits->contains(SpeciesTrait::MEGA), fn (Builder $query) => $query->where('is_mega', true))
            ->when($traits->contains(SpeciesTrait::GMAX), fn (Builder $query) => $query->where('is_gmax', true));
    }

    /**
     * @param  Builder<Species>  $query
     * @param  Collection<int, Generation>  $generations
     * @return Builder<Species>
     */
    private function applyGenerations(Builder $query, Collection $generations): Builder
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
            SearchPokedexSortBy::POKEDEX_NUMBER => $query->orderByPokedexNumber(),
            SearchPokedexSortBy::NAME           => $query->orderBy('name', $dir->value),
        };
    }
}
