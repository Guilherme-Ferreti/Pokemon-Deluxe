<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Filters\SearchPokemonInBoxFilters;
use App\Pokemon\Http\Web\PageProps\PokemonIndexPageProps;
use App\Pokemon\Queries\SearchPokemonInBoxQuery;
use Illuminate\Http\Request;
use Inertia\Response;
use Shared\Base\Controller;

final class PokemonController extends Controller
{
    public function index(Request $request): Response
    {
        $pokemonInParty = fn () => currentUser()
            ->pokemon()
            ->inParty()
            ->get(['id', 'name', 'sprites', 'level', 'is_shiny', 'pokedex_id']);

        $searchPokemonInBoxFilters = SearchPokemonInBoxFilters::fromRequest($request);

        $pokemonInBox = fn () => new SearchPokemonInBoxQuery()->handle(currentUser(), $searchPokemonInBoxFilters)->paginate(12)->withQueryString();

        return inertia('Pokemon/Index')->with(new PokemonIndexPageProps(
            pokemonInParty: $pokemonInParty,
            pokemonInBox: $pokemonInBox,
            searchFilters: $searchPokemonInBoxFilters,
        ));
    }
}
