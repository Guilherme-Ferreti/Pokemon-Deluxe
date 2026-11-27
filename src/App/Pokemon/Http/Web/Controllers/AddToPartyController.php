<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\AddToPartyAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class AddToPartyController extends Controller
{
    public function __invoke(string $pokemon): RedirectResponse
    {
        $ownedPokemon = currentUser()->pokemon()->findOrFail($pokemon);

        new AddToPartyAction()->handle($ownedPokemon);

        ToastHelper::success('Added to party', "{$ownedPokemon->getDisplayName()} joined your party.");

        return back();
    }
}
