<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\SetActiveMoveAction;
use App\Pokemon\Http\Web\Requests\SetActiveMoveRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Models\Move;

final class SetActiveMoveController extends Controller
{
    public function __invoke(SetActiveMoveRequest $request, string $pokemon): RedirectResponse
    {
        new SetActiveMoveAction()->handle(
            pokemon: currentUser()->pokemon()->findOrFail($pokemon),
            move: Move::query()->findOrFail($request->string('move_id')->toString()),
            slot: $request->integer('slot'),
        );

        return back();
    }
}
