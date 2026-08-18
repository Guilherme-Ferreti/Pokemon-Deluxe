<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use App\Dex\Enums\SearchPokedexSortBy;
use App\Dex\Filters\SearchPokedexFilters;
use Illuminate\Database\Eloquent\Builder;
use Shared\Enums\SortDirection;
use Shared\Models\Species;

final class SearchPokedexQuery
{
    /**
     * @return Builder<Species>
     */
    public function handle(SearchPokedexFilters $filters): Builder
    {
        return Species::query()
            ->when($filters->search !== null, fn (Builder $query) => $this->applySearch($query, $filters->search))
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
     * @return Builder<Species>
     */
    private function applySorting(Builder $query, SearchPokedexSortBy $sort, SortDirection $dir): Builder
    {
        return match ($sort) {
            SearchPokedexSortBy::POKEDEX_NUMBER => $query->orderBy('pokedex_number')
                ->orderByRaw('
                    CASE
                        WHEN is_mega = 0 AND is_gmax = 0 THEN 1
                        WHEN is_mega = 1 AND is_gmax = 0 THEN 2
                        WHEN is_mega = 0 AND is_gmax = 1 THEN 3
                    END
                ')
                ->orderBy('id'),
            SearchPokedexSortBy::NAME => $query->orderBy('name', $dir->value),
        };
    }
}
