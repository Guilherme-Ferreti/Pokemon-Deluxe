<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use App\Dex\Enums\SearchAttackdexSortBy;
use App\Dex\Filters\SearchAttackdexFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\MoveCategory;
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
            ->when($filters->types->isNotEmpty(), fn (Builder $query) => $this->applyTypes($query, $filters->types))
            ->when($filters->ailments->isNotEmpty(), fn (Builder $query) => $this->applyAilments($query, $filters->ailments))
            ->when($filters->isZmove, fn (Builder $query) => $this->applyIsZmove($query))
            ->when($filters->isDynamax, fn (Builder $query) => $this->applyIsDynamax($query))
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
     * @param  Collection<int, ElementalType>  $types
     * @return Builder<Move>
     */
    private function applyTypes(Builder $query, Collection $types): Builder
    {
        return $query->whereIn('type', $types->pluck('value'));
    }

    /**
     * @param  Builder<Move>  $query
     * @param  Collection<int, Ailment>  $ailments
     * @return Builder<Move>
     */
    private function applyAilments(Builder $query, Collection $ailments): Builder
    {
        return $query->whereIn('ailment', $ailments->pluck('value'));
    }

    /**
     * @param  Builder<Move>  $query
     * @return Builder<Move>
     */
    private function applyIsZmove(Builder $query): Builder
    {
        return $query->where('is_zmove', true);
    }

    /**
     * @param  Builder<Move>  $query
     * @return Builder<Move>
     */
    private function applyIsDynamax(Builder $query): Builder
    {
        return $query->where('is_dynamax', true);
    }

    /**
     * @param  Builder<Move>  $query
     * @return Builder<Move>
     */
    private function applySorting(Builder $query, SearchAttackdexSortBy $sort, SortDirection $dir): Builder
    {
        return match ($sort) {
            SearchAttackdexSortBy::NAME           => $query->orderBy('name', $dir->value),
            SearchAttackdexSortBy::POWER          => $query->orderByRaw('CASE WHEN category = ? THEN ? ELSE ? END', [MoveCategory::OHKO->value, $dir === SortDirection::ASC ? 1 : 0, $dir === SortDirection::ASC ? 0 : 1])->orderBy('power', $dir->value),
            SearchAttackdexSortBy::ACCURACY       => $query->orderBy('accuracy', $dir->value),
            SearchAttackdexSortBy::AILMENT_CHANCE => $query->orderBy('ailment_chance', $dir->value),
            SearchAttackdexSortBy::FLINCH_CHANCE  => $query->orderBy('flinch_chance', $dir->value),
        };
    }
}
