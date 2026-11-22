<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\Controllers;

use App\Exploration\Http\Web\PageProps\ExploreIndexPageProps;
use App\Exploration\Http\Web\PageProps\ExploreShowPageProps;
use Inertia\Response;
use Shared\Base\Controller;
use Shared\Enums\Area;

final class ExploreController extends Controller
{
    public function index(): Response
    {
        return inertia('Exploration::Explore/Index')->with(new ExploreIndexPageProps);
    }

    public function show(Area $area): Response
    {
        return inertia('Exploration::Explore/Show')->with(new ExploreShowPageProps($area));
    }
}
