<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\EggIncubator;

use App\Pokemon\Exceptions\EggIncubationNotReadyToHatchException;
use App\Pokemon\Exceptions\EggIncubatorNotActiveException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Enums\EggIncubationStatus;
use Shared\Models\EggIncubation;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

final class HatchEggAction
{
    private User $user;

    private EggIncubatorIndex $eggIncubatorIndex;

    private EggIncubation $eggIncubation;

    public function handle(User $user, EggIncubatorIndex $eggIncubatorIndex): EggIncubation
    {
        $this->user              = $user;
        $this->eggIncubatorIndex = $eggIncubatorIndex;

        Cache::withoutOverlapping(
            key: LockKeys::eggIncubatorHatchEgg($this->user->id, $this->eggIncubatorIndex),
            callback: fn () => $this->hatchEgg()
        );

        return $this->eggIncubation;
    }

    private function hatchEgg(): void
    {
        $this->ensureEggIncubatorIsActive();

        $this->loadEggIncubation();

        $this->ensureEggIsReadyToHatch();

        $this->markEggAsHatched();
    }

    private function ensureEggIncubatorIsActive(): void
    {
        $availability = new GetEggIncubatorsAvailabilityAction()->handle($this->user);

        if (value_object_array_contains($availability['active'], $this->eggIncubatorIndex)) {
            return;
        }

        throw new EggIncubatorNotActiveException($this->eggIncubatorIndex);
    }

    private function loadEggIncubation(): void
    {
        $this->eggIncubation = $this->user
            ->eggIncubations()
            ->active()
            ->where('egg_incubator_index', $this->eggIncubatorIndex->value)
            ->firstOrFail();
    }

    private function ensureEggIsReadyToHatch(): void
    {
        if ($this->eggIncubation->getStatus() === EggIncubationStatus::READY_TO_HATCH) {
            return;
        }

        throw new EggIncubationNotReadyToHatchException;
    }

    private function markEggAsHatched(): void
    {
        $this->eggIncubation->hatched_at = now();
        $this->eggIncubation->save();
    }
}
