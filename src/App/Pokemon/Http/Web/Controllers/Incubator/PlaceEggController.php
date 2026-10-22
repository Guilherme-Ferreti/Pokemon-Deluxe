<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\Incubator;

use App\Pokemon\Actions\Incubator\PlaceEggAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\ValueObjects\IncubatorIndex;

final class PlaceEggController extends Controller
{
    public function __invoke(IncubatorIndex $incubatorIndex): RedirectResponse
    {
        new PlaceEggAction()->handle(currentUser(), $incubatorIndex);

        ToastHelper::success('Egg placed', 'Your egg is now incubating.');

        return back();
    }
}
