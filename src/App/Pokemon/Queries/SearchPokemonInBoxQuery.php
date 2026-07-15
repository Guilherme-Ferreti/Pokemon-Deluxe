<?php

declare(strict_types=1);

namespace App\Pokemon\Queries;

use App\Pokemon\Filters\SearchPokemonInBoxFilters;
use Illuminate\Database\Eloquent\Builder;
use Shared\Enums\PokemonCharacteristic;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class SearchPokemonInBoxQuery
{
    private User $user;

    private SearchPokemonInBoxFilters $filters;

    /** @var Builder<Pokemon> */
    private Builder $query;

    /**
     * @return Builder<Pokemon>
     */
    public function handle(User $user, SearchPokemonInBoxFilters $filters): Builder
    {
        $this->user    = $user;
        $this->filters = $filters;

        $this->createBaseQuery();

        $this->applySearchFilter();
        $this->applyCharacteristicFilters();
        $this->applyGenerationFilters();

        return $this->query;
    }

    private function createBaseQuery(): void
    {
        $this->query = Pokemon::query()
            ->where('user_id', $this->user->id)
            ->inBox();
    }

    private function applySearchFilter(): void
    {
        $this->query->when(
            $this->filters->search !== null,
            fn (Builder $query) => $query->whereRelation('pokedex', fn (Builder $query) => $query->whereLike('name', '%' . $this->filters->search . '%'))
        );
    }

    private function applyCharacteristicFilters(): void
    {
        $this->query->when(
            $this->filters->characteristics->isNotEmpty(),
            fn (Builder $query) => $query
                ->when($this->filters->characteristics->contains(PokemonCharacteristic::SHINY), fn (Builder $query) => $query->where('is_shiny', true))
                ->when($this->filters->characteristics->contains(PokemonCharacteristic::BABY), fn (Builder $query) => $query->whereRelation('pokedex', 'is_baby', true))
                ->when($this->filters->characteristics->contains(PokemonCharacteristic::LEGENDARY), fn (Builder $query) => $query->whereRelation('pokedex', 'is_legendary', true))
                ->when($this->filters->characteristics->contains(PokemonCharacteristic::MYTHICAL), fn (Builder $query) => $query->whereRelation('pokedex', 'is_mythical', true))
                ->when($this->filters->characteristics->contains(PokemonCharacteristic::MEGA), fn (Builder $query) => $query->whereRelation('pokedex', 'is_mega', true))
                ->when($this->filters->characteristics->contains(PokemonCharacteristic::GMAX), fn (Builder $query) => $query->whereRelation('pokedex', 'is_gmax', true))
        );
    }

    private function applyGenerationFilters(): void
    {
        $this->query->when(
            $this->filters->generations->isNotEmpty(),
            fn (Builder $query) => $query->whereRelation('pokedex', fn (Builder $query) => $query->whereIn('generation', $this->filters->generations))
        );
    }
}
