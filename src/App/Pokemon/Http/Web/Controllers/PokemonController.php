<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\PokemonShowPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class PokemonController extends Controller
{
    public function show(string $pokemon): Response
    {
        return inertia('Pokemon::Pokemon/Show')->with(new PokemonShowPageProps(
            currentUser()->pokemon()->findOrFail($pokemon),
        ));
    }
}
