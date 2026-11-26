<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\UseBoostStonesAction;
use App\Pokemon\Http\Web\Requests\UseBoostStonesRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Shared\Base\Controller;

final class UseBoostStonesController extends Controller
{
    public function __invoke(UseBoostStonesRequest $request, string $pokemon): RedirectResponse
    {
        $boostResult = new UseBoostStonesAction()->handle(
            pokemon: currentUser()->pokemon()->findOrFail($pokemon),
            maxStones: $request->integer('max_stones'),
            targetBoost: $request->filled('target_boost') ? $request->integer('target_boost') : null,
        );

        Inertia::flash('boostResult', [
            'stonesUsed'  => $boostResult->stonesUsed,
            'successes'   => $boostResult->successes,
            'failures'    => $boostResult->failures,
            'boostBefore' => $boostResult->boostBefore,
            'boostAfter'  => $boostResult->boostAfter,
        ]);

        return back();
    }
}
