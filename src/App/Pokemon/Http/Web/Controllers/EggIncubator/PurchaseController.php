<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\EggIncubator;

use App\Pokemon\Actions\EggIncubator\PurchaseAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\ValueObjects\EggIncubatorIndex;

final class PurchaseController extends Controller
{
    public function __invoke(EggIncubatorIndex $eggIncubatorIndex): RedirectResponse
    {
        new PurchaseAction()->handle(currentUser(), $eggIncubatorIndex);

        ToastHelper::success('Egg incubator purchased', 'You have successfully purchased an egg incubator.');

        return back();
    }
}
