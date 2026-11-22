<?php

declare(strict_types=1);

namespace App\Exploration\Http\Web\Controllers;

use App\Exploration\Actions\BattleEncounterAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class BattleEncounterController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        new BattleEncounterAction()->handle(currentUser());

        return to_route('battle.web.show');
    }
}
