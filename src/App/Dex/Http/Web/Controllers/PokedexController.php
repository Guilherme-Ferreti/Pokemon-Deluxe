<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\Controllers;

use App\Dex\Http\Web\PageProps\PokedexIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class PokedexController extends Controller
{
    public function index(): Response
    {
        return inertia('Dex/Pokedex/Index')->with(new PokedexIndexPageProps);
    }
}
