<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\SwapPartyPokemonAction;
use App\Pokemon\Http\Web\Requests\SwapPartyPokemonRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class SwapPartyPokemonController extends Controller
{
    public function __invoke(SwapPartyPokemonRequest $request, string $pokemon): RedirectResponse
    {
        $joining = currentUser()->pokemon()->findOrFail($pokemon);
        $leaving = currentUser()->pokemon()->findOrFail($request->string('leavingPokemonId')->toString());

        new SwapPartyPokemonAction()->handle($joining, $leaving);

        ToastHelper::success('Added to party', "{$joining->getDisplayName()} joined your party and {$leaving->getDisplayName()} went to your Box.");

        return back();
    }
}
