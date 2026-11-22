<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\Controllers;

use App\Exploration\Http\Web\PageProps\ExploreIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class ExploreController extends Controller
{
    public function index(): Response
    {
        return inertia('Exploration::Explore/Index')->with(new ExploreIndexPageProps);
    }
}
