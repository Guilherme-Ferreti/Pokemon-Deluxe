<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\HatchEggAction;
use App\Pokemon\Actions\PlaceEggAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class EggIncubatorActionController extends Controller
{
    public function placeEgg(int $eggIncubatorIndex): RedirectResponse
    {
        new PlaceEggAction()->handle(currentUser(), $eggIncubatorIndex);

        ToastHelper::success('Egg placed', 'Your egg is now incubating.');

        return back();
    }

    public function hatchEgg(int $eggIncubatorIndex): RedirectResponse
    {
        $eggIncubation = new HatchEggAction()->handle(currentUser(), $eggIncubatorIndex);

        $name = $eggIncubation->species->getDisplayName($eggIncubation->is_shiny);

        ToastHelper::success('Egg hatched', "A {$name} has hatched! Catch it to add it to your collection.");

        return back();
    }

    public function capturePokemon(int $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }
}
