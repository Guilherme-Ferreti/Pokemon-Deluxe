<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\Controllers;

use App\Dex\Http\Web\PageProps\PokedexIndexPageProps;
use App\Dex\Http\Web\PageProps\PokedexShowPageProps;
use Inertia\Response;
use Shared\Base\Controller;
use Shared\Models\Species;

final class PokedexController extends Controller
{
    public function index(): Response
    {
        return inertia('Dex::Pokedex/Index')->with(new PokedexIndexPageProps);
    }

    public function show(Species $species): Response
    {
        return inertia('Dex::Pokedex/Show', ['species' => $species])->with(new PokedexShowPageProps($species));
    }
}
