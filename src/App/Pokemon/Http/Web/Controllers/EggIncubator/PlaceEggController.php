<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\EggIncubator;

use App\Pokemon\Actions\EggIncubator\PlaceEggAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\ValueObjects\EggIncubatorIndex;

final class PlaceEggController extends Controller
{
    public function __invoke(EggIncubatorIndex $eggIncubatorIndex): RedirectResponse
    {
        new PlaceEggAction()->handle(currentUser(), $eggIncubatorIndex);

        ToastHelper::success('Egg placed', 'Your egg is now incubating.');

        return back();
    }
}
