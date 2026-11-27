<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\ReleasePokemonAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class ReleasePokemonController extends Controller
{
    public function __invoke(string $pokemon): RedirectResponse
    {
        $ownedPokemon = currentUser()->pokemon()->findOrFail($pokemon);

        new ReleasePokemonAction()->handle($ownedPokemon);

        ToastHelper::success('Released', "{$ownedPokemon->getDisplayName()} was released.");

        return to_route('pokemon.web.box.index');
    }
}
