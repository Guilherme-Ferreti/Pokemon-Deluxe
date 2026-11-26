<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\ReorderActiveMovesAction;
use App\Pokemon\Http\Web\Requests\ReorderActiveMovesRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class ReorderActiveMovesController extends Controller
{
    public function __invoke(ReorderActiveMovesRequest $request, string $pokemon): RedirectResponse
    {
        /** @var list<string> $moveIds */
        $moveIds = $request->validated('move_ids');

        new ReorderActiveMovesAction()->handle(currentUser()->pokemon()->findOrFail($pokemon), $moveIds);

        return back();
    }
}
