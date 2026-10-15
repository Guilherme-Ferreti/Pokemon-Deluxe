<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\EggIncubator;

use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class CapturePokemonController extends Controller
{
    public function __invoke(int $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }
}
