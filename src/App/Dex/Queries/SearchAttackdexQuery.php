<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use App\Dex\Enums\MoveTrait;
use App\Dex\Enums\SearchAttackdexSortBy;
use App\Dex\Filters\SearchAttackdexFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonType;
use Shared\Enums\SortDirection;
use Shared\Models\Move;

final class SearchAttackdexQuery
{
    /**
     * @return Builder<Move>
     */
    public function handle(SearchAttackdexFilters $filters): Builder
    {
        return Move::query()
            ->when($filters->search !== null, fn (Builder $query) => $this->applySearch($query, $filters->search))
            ->when($filters->types !== [], fn (Builder $query) => $this->applyTypes($query, $filters->types))
            ->when($filters->ailments !== [], fn (Builder $query) => $this->applyAilments($query, $filters->ailments))
            ->when($filters->traits !== [], fn (Builder $query) => $this->applyTraits($query, $filters->traits))
            ->when(true, fn (Builder $query) => $this->applySorting($query, $filters->sort, $filters->dir));
    }

    /**
     * @param  Builder<Move>  $query
     * @return Builder<Move>
     */
    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->whereLike('name', '%' . $search . '%');
    }

    /**
     * @param  Builder<Move>  $query
     * @param  list<PokemonType>  $types
     * @return Builder<Move>
     */
    private function applyTypes(Builder $query, array $types): Builder
    {
        return $query->whereIn('type', Arr::pluck($types, 'value'));
    }

    /**
     * @param  Builder<Move>  $query
     * @param  list<Ailment>  $ailments
     * @return Builder<Move>
     */
    private function applyAilments(Builder $query, array $ailments): Builder
    {
        return $query->whereIn('ailment', Arr::pluck($ailments, 'value'));
    }

    /**
     * @param  Builder<Move>  $query
     * @param  list<MoveTrait>  $traits
     * @return Builder<Move>
     */
    private function applyTraits(Builder $query, array $traits): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->when(in_array(MoveTrait::Z_MOVE, $traits, true), fn (Builder $query) => $query->orWhere('is_z_move', true))
            ->when(in_array(MoveTrait::DYNAMAX_MOVE, $traits, true), fn (Builder $query) => $query->orWhere('is_dynamax_move', true))
        );
    }

    /**
     * @param  Builder<Move>  $query
     * @return Builder<Move>
     */
    private function applySorting(Builder $query, SearchAttackdexSortBy $sort, SortDirection $dir): Builder
    {
        return match ($sort) {
            SearchAttackdexSortBy::NAME           => $query->orderBy('name', $dir->value),
            SearchAttackdexSortBy::POWER          => $query->orderBy('power', $dir->value),
            SearchAttackdexSortBy::ACCURACY       => $query->orderBy('accuracy', $dir->value),
            SearchAttackdexSortBy::AILMENT_CHANCE => $query->orderBy('ailment_chance', $dir->value),
            SearchAttackdexSortBy::FLINCH_CHANCE  => $query->orderBy('flinch_chance', $dir->value),
        };
    }
}
