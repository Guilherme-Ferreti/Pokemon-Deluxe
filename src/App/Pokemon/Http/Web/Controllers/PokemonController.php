<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\PokemonIndexPageProps;
use App\Pokemon\Queries\SearchPokemonInBoxQuery;
use Inertia\Response;
use Shared\Base\Controller;

final class PokemonController extends Controller
{
    public function index(): Response
    {
        $pokemonInParty = fn () => currentUser()
            ->pokemon()
            ->inParty()
            ->get(['id', 'name', 'sprites', 'level', 'is_shiny', 'pokedex_id']);

        $pokemonInBox = fn () => new SearchPokemonInBoxQuery()
            ->handle(currentUser())
            ->paginate(12)
            ->withQueryString();

        return inertia('Pokemon/Index')->with(new PokemonIndexPageProps(
            pokemonInParty: $pokemonInParty,
            pokemonInBox: $pokemonInBox
        ));
    }
}
