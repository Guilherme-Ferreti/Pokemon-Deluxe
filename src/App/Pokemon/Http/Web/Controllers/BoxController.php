<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\BoxIndexPageProps;
use App\Pokemon\Http\Web\PageProps\BoxShowPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class BoxController extends Controller
{
    public function index(): Response
    {
        return inertia('Pokemon::Box/Index')->with(new BoxIndexPageProps);
    }

    public function show(string $pokemon): Response
    {
        return inertia('Pokemon::Box/Show')->with(new BoxShowPageProps(
            currentUser()->pokemon()->findOrFail($pokemon),
        ));
    }
}
