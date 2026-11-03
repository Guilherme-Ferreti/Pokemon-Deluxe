<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\Incubator;

use App\Pokemon\Actions\Incubator\CatchPokemonAction;
use App\Pokemon\Http\Web\Requests\Incubator\CatchPokemonRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\Models\Pokeball;
use Shared\ValueObjects\IncubatorIndex;

final class CatchPokemonController extends Controller
{
    public function __invoke(IncubatorIndex $incubatorIndex, CatchPokemonRequest $request): RedirectResponse
    {
        /** @var Pokeball */
        $pokeball = Pokeball::findOrFail($request->validated('pokeballId'));

        $pokemon = new CatchPokemonAction()->handle(
            user: currentUser(),
            incubatorIndex: $incubatorIndex,
            pokeball: $pokeball,
        );

        ToastHelper::success('Congratulations!', "You caught {$pokemon->getDisplayName()}!");

        return back();
    }
}
