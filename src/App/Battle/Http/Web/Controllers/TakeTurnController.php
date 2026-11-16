<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Controllers;

use App\Battle\Actions\TakeTurnAction;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use App\Battle\Http\Web\Requests\TakeTurnRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class TakeTurnController extends Controller
{
    public function __invoke(TakeTurnRequest $request): RedirectResponse
    {
        $battle = currentUser()->battles()->ongoing()->firstOr(fn () => throw new BattleAlreadyEndedException);

        new TakeTurnAction()->handle($battle, $request->integer('moveSlot'));

        return back();
    }
}
