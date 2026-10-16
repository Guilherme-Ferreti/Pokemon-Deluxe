<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\EggIncubator;

use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\ValueObjects\EggIncubatorIndex;

final class CapturePokemonController extends Controller
{
    public function __invoke(EggIncubatorIndex $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }
}
