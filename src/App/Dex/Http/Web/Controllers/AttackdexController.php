<?php

declare(strict_types=1);

namespace App\Dex\Http\Web\Controllers;

use App\Dex\Http\Web\PageProps\AttackdexIndexPageProps;
use Inertia\Response;
use Shared\Base\Controller;

final class AttackdexController extends Controller
{
    public function index(): Response
    {
        return inertia('Dex::Attackdex/Index')->with(new AttackdexIndexPageProps);
    }
}
