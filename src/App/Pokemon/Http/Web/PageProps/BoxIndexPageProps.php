<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\PageProps;

use App\Pokemon\Filters\SearchBoxFilters;
use App\Pokemon\Queries\SearchBoxQuery;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
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
            'pokemonList'   => fn () => $this->getPokemonListProp($searchBoxFilters),
            'activeFilters' => fn () => $searchBoxFilters,
            'filterOptions' => fn () => SearchBoxFilters::filterOptions(),
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
            'name'        => $pokemon->getName(),
            'frontSprite' => $pokemon->getFrontSprite(),
            'level'       => $pokemon->level,
            'experience'  => $pokemon->experience,
            'species'     => [
                'types' => $pokemon->species->types,
            ],
            'moves' => [
                [
                    'name' => 'Giga Impact',
                ],
                [
                    'name' => 'Flamethrower',
                ],
                [
                    'name' => 'Earthquake',
                ],
                [
                    'name' => 'Ice Beam',
                ],
            ],
        ]);
    }
}
