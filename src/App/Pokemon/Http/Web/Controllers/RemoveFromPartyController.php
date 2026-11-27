<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\RemoveFromPartyAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class RemoveFromPartyController extends Controller
{
    public function __invoke(string $pokemon): RedirectResponse
    {
        $ownedPokemon = currentUser()->pokemon()->findOrFail($pokemon);

        new RemoveFromPartyAction()->handle($ownedPokemon);

        ToastHelper::success('Removed from party', "{$ownedPokemon->getDisplayName()} went to your Box.");

        return back();
    }
}
