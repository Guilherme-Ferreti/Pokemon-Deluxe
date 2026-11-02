<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Http\Web\PageProps\NurseryPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class NurseryController extends Controller
{
    public function __invoke(): Response
    {
        return inertia('Pokemon::Nursery')->with(new NurseryPageProps);
    }
}
