<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\BoxIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class BoxController extends Controller
{
    public function index(): Response
    {
        return inertia('Pokemon/Box/Index')->with(new BoxIndexPageProps);
    }
}
