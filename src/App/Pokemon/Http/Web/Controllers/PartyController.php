<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\PartyPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class PartyController extends Controller
{
    public function __invoke(): Response
    {
        $party = fn () => currentUser()
            ->pokemon()
            ->inParty()
            ->get(['id', 'name', 'sprites', 'level', 'is_shiny', 'pokedex_id']);

        return inertia('Pokemon/Party')->with(new PartyPageProps(
            party: $party,
        ));
    }
}
