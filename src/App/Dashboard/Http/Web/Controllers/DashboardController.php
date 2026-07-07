<?php

declare(strict_types=1);

namespace App\Dashboard\Http\Web\Controllers;

use Inertia\Response;
use Shared\Base\Controller;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return inertia('Dashboard/Dashboard');
    }
}
