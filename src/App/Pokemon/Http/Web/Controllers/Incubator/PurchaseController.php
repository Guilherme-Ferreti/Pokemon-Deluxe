<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers\Incubator;

use App\Pokemon\Actions\Incubator\PurchaseAction;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\ValueObjects\IncubatorIndex;

final class PurchaseController extends Controller
{
    public function __invoke(IncubatorIndex $incubatorIndex): RedirectResponse
    {
        new PurchaseAction()->handle(currentUser(), $incubatorIndex);

        ToastHelper::success('Incubator purchased', 'You have successfully purchased an incubator.');

        return back();
    }
}
