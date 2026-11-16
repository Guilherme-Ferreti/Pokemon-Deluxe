<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Controllers;

use App\Battle\Actions\ForfeitBattleAction;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class ForfeitBattleController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $battle = currentUser()->battles()->ongoing()->firstOr(fn () => throw new BattleAlreadyEndedException);

        new ForfeitBattleAction()->handle($battle);

        return back();
    }
}
