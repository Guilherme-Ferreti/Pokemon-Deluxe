<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\PokemonIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class PokemonController extends Controller
{
    public function index(): Response
    {
        $pokemonInParty = currentUser()->pokemon()->inParty()->get(['id', 'name', 'sprites', 'level', 'is_shiny', 'pokedex_id']);

        $pokemonInBox = currentUser()->pokemon()->inBox()->get();

        return inertia('Pokemon/Index')->with(new PokemonIndexPageProps(
            pokemonInParty: $pokemonInParty,
            pokemonInBox: $pokemonInBox
        ));
    }
}
