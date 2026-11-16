<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Controllers;

use App\Battle\Http\Web\PageProps\BattleShowPageProps;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Shared\Base\Controller;

final class BattleController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $battle = currentUser()->battles()->ongoing()->first()
            ?? currentUser()->battles()->recentlyEnded()->latest('updated_at')->first();

        if ($battle === null) {
            return to_route('dashboard.web.home');
        }

        return inertia('Battle::Battle')->with(new BattleShowPageProps($battle));
    }
}
