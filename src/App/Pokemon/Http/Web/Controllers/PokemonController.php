<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\PokemonIndexPageProps;
use Illuminate\Database\Eloquent\Builder;
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

        $pokemonInBox = fn () => currentUser()
            ->pokemon()
            ->inBox()
            ->when(
                $request->has('search'),
                fn (Builder $query) => $query->whereRelation('pokedex', fn (Builder $query) => $query->whereLike('name', '%' . $request->query('search') . '%'))
            )
            ->paginate(12)
            ->withQueryString();

        return inertia('Pokemon/Index')->with(new PokemonIndexPageProps(
            pokemonInParty: $pokemonInParty,
            pokemonInBox: $pokemonInBox
        ));
    }
}
