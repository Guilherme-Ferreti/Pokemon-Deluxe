<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\HatchEggAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Shared\Base\Controller;
use Shared\Base\DomainException;
use Shared\Models\EggIncubation;

final class HatchEggController extends Controller
{
    public function __invoke(EggIncubation $eggIncubation): RedirectResponse
    {
        Gate::authorize('hatchEgg', $eggIncubation);

        try {
            new HatchEggAction()->handle($eggIncubation);
        } catch (DomainException $domainException) {
            throw ValidationException::withMessages([$domainException->getKey() => $domainException->getMessage()]);
        }

        return back();
    }
}
