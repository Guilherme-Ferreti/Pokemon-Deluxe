<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Filters\SearchBoxFilters;
use App\Pokemon\Queries\SearchBoxQuery;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Move;
use Shared\Models\Pokemon;

final readonly class BoxIndexPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        $searchBoxFilters = SearchBoxFilters::fromRequest($context->request);

        return [
            'pokemonList'  => fn () => $this->getPokemonListProp($searchBoxFilters),
            'party'        => $this->getPartyProp(...),
            'maxPartySize' => (int) config('game.user.max_party_size'),
            'filters'      => [
                'options' => SearchBoxFilters::filterOptions(...),
                'active'  => fn () => $searchBoxFilters,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getPokemonListProp(SearchBoxFilters $searchBoxFilters): array
    {
        $pokemonList = new SearchBoxQuery()->handle(currentUser(), $searchBoxFilters)->paginate(12)->withQueryString();

        return AppHelper::paginatorToArray($pokemonList, fn (Pokemon $pokemon) => [
            'id'          => $pokemon->id,
            'name'        => $pokemon->getDisplayName(),
            'gender'      => $pokemon->gender,
            'frontSprite' => $pokemon->getDisplayFrontSprite(),
            'level'       => $pokemon->level,
            'experience'  => $pokemon->experience,
            'inParty'     => $pokemon->in_party,
            'species'     => [
                'types' => $pokemon->species->types,
                'slug'  => $pokemon->species->slug,
            ],
            'moves' => $pokemon->activeMoves->map(fn (Move $move) => [
                'name' => $move->name,
            ]),
        ]);
    }

    /**
     * @return list<array{id: string, name: string, portrait: string|null, level: int, boost: int}>
     */
    private function getPartyProp(): array
    {
        return array_values(currentUser()
            ->pokemon()
            ->inPartyOrder()
            ->with('species')
            ->get()
            ->map(fn (Pokemon $partyPokemon) => [
                'id'       => $partyPokemon->id,
                'name'     => $partyPokemon->getDisplayName(),
                'portrait' => $partyPokemon->getDisplayPortrait(),
                'level'    => $partyPokemon->level,
                'boost'    => $partyPokemon->boost,
            ])
            ->all());
    }
}
