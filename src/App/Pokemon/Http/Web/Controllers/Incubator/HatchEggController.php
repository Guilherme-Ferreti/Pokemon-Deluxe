<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\Incubator;

use App\Pokemon\Actions\Incubator\HatchEggAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\ValueObjects\IncubatorIndex;

final class HatchEggController extends Controller
{
    public function __invoke(IncubatorIndex $incubatorIndex): RedirectResponse
    {
        $incubation = new HatchEggAction()->handle(currentUser(), $incubatorIndex);

        $name = $incubation->species->getDisplayName($incubation->is_shiny);

        ToastHelper::success('Egg hatched', "A {$name} has hatched! Catch it to add it to your collection.");

        return back();
    }
}
