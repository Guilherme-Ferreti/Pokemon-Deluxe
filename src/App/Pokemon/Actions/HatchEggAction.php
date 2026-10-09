<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Exceptions\EggAlreadyHatchedException;
use App\Pokemon\Exceptions\EggIncubatorIdleException;
use App\Pokemon\Exceptions\EggIncubatorNotPurchasedException;
use App\Pokemon\Exceptions\EggStillIncubatingException;
use Shared\Enums\EggIncubationStatus;
use Shared\Models\EggIncubation;
use Shared\Models\User;

final class HatchEggAction
{
    private User $user;

    private int $eggIncubatorIndex;

    private EggIncubation $eggIncubation;

    public function handle(User $user, int $eggIncubatorIndex): void
    {
        $this->user              = $user;
        $this->eggIncubatorIndex = $eggIncubatorIndex;

        $this->ensureEggIncubatorIsActive();

        $this->eggIncubation = $user->eggIncubations()->active()->where('egg_incubator_index', $eggIncubatorIndex)->firstOrFail();

        $this->ensureEggIsReadyToHatch();

        $this->hatchEgg();
    }

    private function ensureEggIncubatorIsActive(): void
    {
        $availability = new GetEggIncubatorsAvailabilityAction()->handle($this->user);

        if (in_array($this->eggIncubatorIndex, $availability['active'])) {
            return;
        }

        if (in_array($this->eggIncubatorIndex, $availability['notPurchased'])) {
            throw new EggIncubatorNotPurchasedException;
        }

        if (in_array($this->eggIncubatorIndex, $availability['idle'])) {
            throw new EggIncubatorIdleException;
        }
    }

    private function ensureEggIsReadyToHatch(): void
    {
        if ($this->eggIncubation->getStatus() === EggIncubationStatus::READY_TO_HATCH) {
            return;
        }

        if ($this->eggIncubation->getStatus() === EggIncubationStatus::INCUBATING) {
            throw new EggStillIncubatingException;
        }

        if ($this->eggIncubation->getStatus() === EggIncubationStatus::READY_TO_CAPTURE) {
            throw new EggAlreadyHatchedException;
        }
    }

    private function hatchEgg(): void
    {
        $this->eggIncubation->hatched_at = now();
        $this->eggIncubation->save();
    }
}
