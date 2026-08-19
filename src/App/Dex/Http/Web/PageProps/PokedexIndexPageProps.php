<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\PageProps;

use App\Dex\Actions\ListSpeciesBoxPresence;
use App\Dex\Filters\SearchPokedexFilters;
use App\Dex\Queries\SearchPokedexQuery;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Helpers\AppHelper;
use Shared\Models\Species;

final class PokedexIndexPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        $searchPokedexFilters = SearchPokedexFilters::fromRequest($context->request);

        return [
            'speciesList'   => fn () => $this->getSpeciesListProp($searchPokedexFilters),
            'activeFilters' => fn () => $searchPokedexFilters,
            'filterOptions' => fn () => SearchPokedexFilters::filterOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSpeciesListProp(SearchPokedexFilters $searchPokedexFilters): array
    {
        $speciesList = new SearchPokedexQuery()->handle($searchPokedexFilters)->paginate(50)->withQueryString();

        $boxPresence = new ListSpeciesBoxPresence()->handle(currentUser(), $speciesList->pluck('id'));

        return AppHelper::paginatorToArray($speciesList, fn (Species $species) => [
            'id'            => $species->id,
            'portrait'      => $species->portraits->normal,
            'name'          => $species->name,
            'pokedexNumber' => $species->pokedex_number,
            'frontSprite'   => $species->sprites->front_default,
            'types'         => $species->types,
            'generation'    => $species->generation->label(),
            'regularInBox'  => $boxPresence->firstwhere('speciesId', $species->id)['regularInBox'],
            'shinyInBox'    => $boxPresence->firstwhere('speciesId', $species->id)['shinyInBox'],
        ]);
    }
}
