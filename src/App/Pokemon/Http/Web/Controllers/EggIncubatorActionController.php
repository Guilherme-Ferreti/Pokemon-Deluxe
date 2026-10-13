<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\HatchEggAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class EggIncubatorActionController extends Controller
{
    public function placeEgg(int $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }

    public function hatchEgg(int $eggIncubatorIndex): RedirectResponse
    {
        new HatchEggAction()->handle(currentUser(), $eggIncubatorIndex);

        return back();
    }

    public function capturePokemon(int $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }
}
