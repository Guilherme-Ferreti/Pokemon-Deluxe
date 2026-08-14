<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Filters\SearchBoxFilters;
use App\Pokemon\Http\Web\PageProps\BoxIndexPageProps;
use App\Pokemon\Queries\SearchBoxQuery;
use Illuminate\Http\Request;
use Inertia\Response;
use Shared\Base\Controller;

final class BoxController extends Controller
{
    public function index(Request $request): Response
    {
        $searchBoxFilters = SearchBoxFilters::fromRequest($request);

        $pokemonList = fn () => new SearchBoxQuery()->handle(currentUser(), $searchBoxFilters)->paginate(12)->withQueryString();

        return inertia('Pokemon/Box/Index')->with(new BoxIndexPageProps(
            pokemonList: $pokemonList,
            activeFilters: $searchBoxFilters,
        ));
    }
}
