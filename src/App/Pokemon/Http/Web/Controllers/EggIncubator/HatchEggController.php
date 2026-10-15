<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\EggIncubator;

use App\Pokemon\Actions\EggIncubator\HatchEggAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;

final class HatchEggController extends Controller
{
    public function __invoke(int $eggIncubatorIndex): RedirectResponse
    {
        $eggIncubation = new HatchEggAction()->handle(currentUser(), $eggIncubatorIndex);

        $name = $eggIncubation->species->getDisplayName($eggIncubation->is_shiny);

        ToastHelper::success('Egg hatched', "A {$name} has hatched! Catch it to add it to your collection.");

        return back();
    }
}
