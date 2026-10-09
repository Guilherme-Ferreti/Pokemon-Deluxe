<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\HatchEggAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Shared\Base\Controller;
use Shared\Base\DomainException;

final class EggIncubatorActionController extends Controller
{
    public function placeEgg(int $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }

    public function hatchEgg(int $eggIncubatorIndex): RedirectResponse
    {
        try {
            new HatchEggAction()->handle(currentUser(), $eggIncubatorIndex);
        } catch (DomainException $domainException) {
            throw ValidationException::withMessages([$domainException->getKey() => $domainException->getMessage()]);
        }

        return back();
    }

    public function capturePokemon(int $eggIncubatorIndex): RedirectResponse
    {
        return back();
    }
}
