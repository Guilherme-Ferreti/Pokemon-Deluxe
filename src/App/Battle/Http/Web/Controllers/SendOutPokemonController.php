<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Controllers;

use App\Battle\Actions\SendOutPokemonAction;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use App\Battle\Http\Web\Requests\SendOutPokemonRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class SendOutPokemonController extends Controller
{
    public function __invoke(SendOutPokemonRequest $request): RedirectResponse
    {
        $battle = currentUser()->battles()->ongoing()->firstOr(fn () => throw new BattleAlreadyEndedException);

        new SendOutPokemonAction()->handle($battle, $request->integer('combatantIndex'));

        return back();
    }
}
