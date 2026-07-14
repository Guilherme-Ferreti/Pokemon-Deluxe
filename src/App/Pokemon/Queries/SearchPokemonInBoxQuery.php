<?php

declare(strict_types=1);

namespace App\Pokemon\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Shared\Enums\Generation;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class SearchPokemonInBoxQuery
{
    /**
     * @var Builder<Pokemon>
     */
    private Builder $query;

    /**
     * @return Builder<Pokemon>
     */
    public function handle(User $user): Builder
    {
        $this->query = Pokemon::query()
            ->where('user_id', $user->id)
            ->inBox();

        $this->applySearchFilter();
        $this->applyCharacteristicFilters();
        $this->applyGenerationFilters();

        return $this->query;
    }

    private function applySearchFilter(): void
    {
        $this->query->when(
            request()->has('search'),
            fn (Builder $query) => $query->whereRelation(
                'pokedex',
                fn (Builder $query) => $query->whereLike('name', '%' . request()->query('search') . '%')
            )
        );
    }

    private function applyCharacteristicFilters(): void
    {
        $filters = $this->getValidBooleanFiltersFromRequest(['shiny', 'baby', 'legendary', 'mythical', 'mega', 'gmax']);

        $this->query->when(
            $filters->isNotEmpty(),
            fn (Builder $query) => $query
                ->when($filters->contains('shiny'), fn (Builder $query) => $query->where('is_shiny', true))
                ->when($filters->contains('baby'), fn (Builder $query) => $query->whereRelation('pokedex', 'is_baby', true))
                ->when($filters->contains('legendary'), fn (Builder $query) => $query->whereRelation('pokedex', 'is_legendary', true))
                ->when($filters->contains('mythical'), fn (Builder $query) => $query->whereRelation('pokedex', 'is_mythical', true))
                ->when($filters->contains('mega'), fn (Builder $query) => $query->whereRelation('pokedex', 'is_mega', true))
                ->when($filters->contains('gmax'), fn (Builder $query) => $query->whereRelation('pokedex', 'is_gmax', true))
        );
    }

    private function applyGenerationFilters(): void
    {
        $filters = $this->getValidBooleanFiltersFromRequest(['kanto', 'johto', 'hoenn', 'sinnoh', 'unova', 'kalos', 'alola', 'galar', 'paldea']);

        $generations = $filters->map(fn (string $generation) => Generation::fromString($generation)->value)->toArray();

        $this->query->when(
            $filters->isNotEmpty(),
            fn (Builder $query) => $query->whereRelation('pokedex', fn (Builder $query) => $query->whereIn('generation', $generations))
        );
    }

    /**
     * @param  array<string>  $filters
     * @return Collection<int, string>
     */
    private function getValidBooleanFiltersFromRequest(array $filters): Collection
    {
        return request()
            ->collect($filters)
            ->filter(fn (mixed $value) => $value === 'true')
            ->keys();
    }
}
