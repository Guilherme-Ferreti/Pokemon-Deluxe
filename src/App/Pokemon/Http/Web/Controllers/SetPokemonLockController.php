<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\SetPokemonLockAction;
use App\Pokemon\Http\Web\Requests\SetPokemonLockRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class SetPokemonLockController extends Controller
{
    public function __invoke(SetPokemonLockRequest $request, string $pokemon): RedirectResponse
    {
        $ownedPokemon = currentUser()->pokemon()->findOrFail($pokemon);
        $isLocked     = $request->boolean('is_locked');

        new SetPokemonLockAction()->handle($ownedPokemon, $isLocked);

        if ($isLocked) {
            ToastHelper::success('Locked', "{$ownedPokemon->getDisplayName()} can't be released or traded until you unlock it.");
        } else {
            ToastHelper::success('Unlocked', "{$ownedPokemon->getDisplayName()} can be released and traded again.");
        }

        return back();
    }
}
