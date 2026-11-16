<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Controllers;

use App\Battle\Actions\SwitchPokemonAction;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use App\Battle\Http\Web\Requests\SwitchPokemonRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class SwitchPokemonController extends Controller
{
    public function __invoke(SwitchPokemonRequest $request): RedirectResponse
    {
        $battle = currentUser()->battles()->ongoing()->firstOr(fn () => throw new BattleAlreadyEndedException);

        new SwitchPokemonAction()->handle($battle, $request->integer('combatantIndex'));

        return back();
    }
}
