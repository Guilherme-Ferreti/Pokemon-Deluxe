<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\Incubator;

use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\ValueObjects\IncubatorIndex;

final class CapturePokemonController extends Controller
{
    public function __invoke(IncubatorIndex $incubatorIndex): RedirectResponse
    {
        dd($incubatorIndex);
        return back();
    }
}
