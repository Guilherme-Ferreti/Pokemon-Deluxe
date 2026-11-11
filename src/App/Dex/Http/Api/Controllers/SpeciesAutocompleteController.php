<?php

declare(strict_types=1);

namespace App\Dex\Http\Api\Controllers;

use App\Dex\Enums\SearchPokedexSortBy;
use App\Dex\Filters\SearchPokedexFilters;
use App\Dex\Queries\SearchPokedexQuery;
use Illuminate\Http\Request;
use Shared\Base\Controller;
use Shared\Enums\SortDirection;
use Shared\Models\Species;

final class SpeciesAutocompleteController extends Controller
{
    /**
     * @return array<int|string, mixed>
     */
    public function __invoke(Request $request): array
    {
        $search = $request->query('search');

        if (! is_string($search) || $search === '') {
            return [];
        }

        $searchPokedexFilters = new SearchPokedexFilters(
            search: $search,
            types: collect(),
            traits: collect(),
            generations: collect(),
            sort: SearchPokedexSortBy::POKEDEX_NUMBER,
            dir: SortDirection::ASC,
        );

        return new SearchPokedexQuery()
            ->handle($searchPokedexFilters)
            ->select('name', 'pokedex_number', 'slug', 'portraits')
            ->limit(5)
            ->get()
            ->map(fn (Species $species) => [
                'name'          => $species->name,
                'pokedexNumber' => $species->pokedex_number,
                'portrait'      => $species->portraits->regular,
                'slug'          => $species->slug,
            ])
            ->toArray();
    }
}
