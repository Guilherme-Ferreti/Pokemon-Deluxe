<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\ReorderPartyAction;
use App\Pokemon\Http\Web\Requests\ReorderPartyRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;

final class ReorderPartyController extends Controller
{
    public function __invoke(ReorderPartyRequest $request): RedirectResponse
    {
        /** @var list<string> $pokemonIds */
        $pokemonIds = $request->validated('pokemonIds');

        new ReorderPartyAction()->handle(currentUser(), $pokemonIds);

        return back();
    }
}
