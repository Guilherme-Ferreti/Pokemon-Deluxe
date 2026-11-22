<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\Controllers;

use App\Exploration\Actions\ExploreAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Enums\Area;

final class ExploreAreaController extends Controller
{
    public function __invoke(Area $area): RedirectResponse
    {
        new ExploreAction()->handle(currentUser(), $area);

        return to_route('exploration.web.show', $area);
    }
}
