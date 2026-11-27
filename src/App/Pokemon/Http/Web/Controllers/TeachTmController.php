<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\TeachTmAction;
use App\Pokemon\Http\Web\Requests\TeachTmRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\Models\Move;

final class TeachTmController extends Controller
{
    public function __invoke(TeachTmRequest $request, string $pokemon): RedirectResponse
    {
        $ownedPokemon = currentUser()->pokemon()->findOrFail($pokemon);
        $move         = $request->modelOrFail('moveId', Move::class);

        new TeachTmAction()->handle($ownedPokemon, $move);

        ToastHelper::success('Move learned', "{$ownedPokemon->getDisplayName()} learned {$move->name}.");

        return back();
    }
}
