<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\Controllers;

use App\Dex\Http\Web\PageProps\PokedexIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;
use Shared\Models\Pokedex;

final class PokedexController extends Controller
{
    public function index(): Response
    {
        $pokedex = fn () => Pokedex::query()
            ->orderBy('generation')
            ->orderBy('pokedex_number')
            ->orderByRaw('
                CASE
                    WHEN is_mega = 0 AND is_gmax = 0 THEN 1
                    WHEN is_mega = 1 AND is_gmax = 0 THEN 2
                    WHEN is_mega = 0 AND is_gmax = 1 THEN 3
                END
            ')
            ->orderBy('id')
            ->paginate(50);

        return inertia('Dex/Pokedex/Index')->with(new PokedexIndexPageProps(
            pokedex: $pokedex
        ));
    }
}
