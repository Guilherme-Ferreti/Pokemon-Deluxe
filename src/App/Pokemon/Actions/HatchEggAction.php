<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Exceptions\EggAlreadyHatchedException;
use App\Pokemon\Exceptions\EggIncubationAlreadyCompletedException;
use App\Pokemon\Exceptions\EggStillIncubatingException;
use Shared\Enums\EggIncubationStatus;
use Shared\Models\EggIncubation;

final class HatchEggAction
{
    public function handle(EggIncubation $eggIncubation): void
    {
        $this->ensureEggCanBeHatched($eggIncubation);

        $eggIncubation->hatched_at = now();
        $eggIncubation->save();
    }

    private function ensureEggCanBeHatched(EggIncubation $eggIncubation): void
    {
        match ($eggIncubation->getStatus()) {
            EggIncubationStatus::INCUBATING       => throw new EggStillIncubatingException,
            EggIncubationStatus::READY_TO_HATCH   => null,
            EggIncubationStatus::READY_TO_CAPTURE => throw new EggAlreadyHatchedException,
            EggIncubationStatus::COMPLETED        => throw new EggIncubationAlreadyCompletedException,
        };
    }
}
