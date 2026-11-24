<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Controllers;

use App\Battle\Actions\ThrowBallAction;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use App\Battle\Http\Web\Requests\ThrowBallRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Models\Pokeball;

final class ThrowBallController extends Controller
{
    public function __invoke(ThrowBallRequest $request): RedirectResponse
    {
        $battle = currentUser()->battles()->ongoing()->firstOr(fn () => throw new BattleAlreadyEndedException);

        $pokeball = Pokeball::query()->where('slug', $request->validated('pokeball'))->firstOrFail();

        new ThrowBallAction()->handle($battle, $pokeball);

        return back();
    }
}
