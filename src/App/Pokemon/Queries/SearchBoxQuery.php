<?php

declare(strict_types=1);

namespace App\Pokemon\Queries;

use App\Pokemon\Enums\SearchBoxSortBy;
use App\Pokemon\Filters\SearchBoxFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\PokemonTrait;
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
            ->join('pokedex', 'pokedex.id', '=', 'pokemon.pokedex_id')
            ->select('pokemon.*');
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @return Builder<Pokemon>
     */
    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->whereLike('pokedex.name', '%' . $search . '%');
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @param  Collection<int, ElementalType>  $types
     * @return Builder<Pokemon>
     */
    private function applyTypes(Builder $query, Collection $types): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->when(
                $types->count() === 1,
                fn (Builder $query) => $query->whereJsonContains('pokedex.types', $types[0]->value)
            )
            ->when(
                $types->count() === 2,
                fn (Builder $query) => $query->whereJsonContains('pokedex.types', $types[0]->value)->whereJsonContains('pokedex.types', $types[1]->value)
            )
        );
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
            ->when($traits->contains(PokemonTrait::BABY), fn (Builder $query) => $query->where('pokedex.is_baby', true))
            ->when($traits->contains(PokemonTrait::LEGENDARY), fn (Builder $query) => $query->where('pokedex.is_legendary', true))
            ->when($traits->contains(PokemonTrait::MYTHICAL), fn (Builder $query) => $query->where('pokedex.is_mythical', true))
            ->when($traits->contains(PokemonTrait::MEGA), fn (Builder $query) => $query->where('pokedex.is_mega', true))
            ->when($traits->contains(PokemonTrait::GMAX), fn (Builder $query) => $query->where('pokedex.is_gmax', true));
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @param  Collection<int, Generation>  $generations
     * @return Builder<Pokemon>
     */
    private function applyGenerations(Builder $query, Collection $generations): Builder
    {
        return $query->whereIn('pokedex.generation', $generations);
    }

    /**
     * @param  Builder<Pokemon>  $query
     * @return Builder<Pokemon>
     */
    private function applySorting(Builder $query, SearchBoxSortBy $sort, SortDirection $dir): Builder
    {
        return match ($sort) {
            SearchBoxSortBy::CAPTURE_DATE   => $query->orderBy('pokemon.captured_at', $dir->value),
            SearchBoxSortBy::EXPERIENCE     => $query->orderBy('pokemon.experience', $dir->value),
            SearchBoxSortBy::LEVEL          => $query->orderBy('pokemon.level', $dir->value),
            SearchBoxSortBy::POKEDEX_NUMBER => $query->orderBy('pokedex.pokedex_number', $dir->value),
        };
    }
}
