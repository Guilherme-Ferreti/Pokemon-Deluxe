<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Exceptions\IncubationNotReadyToHatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Enums\IncubationStatus;
use Shared\Models\Incubation;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class HatchEggAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    private Incubation $incubation;

    public function handle(User $user, IncubatorIndex $incubatorIndex): Incubation
    {
        $this->user           = $user;
        $this->incubatorIndex = $incubatorIndex;

        Cache::withoutOverlapping(
            key: LockKeys::incubatorHatchEgg($this->user->id, $this->incubatorIndex),
            callback: fn () => $this->hatchEgg()
        );

        return $this->incubation;
    }

    private function hatchEgg(): void
    {
        $this->ensureIncubatorIsActive();

        $this->loadIncubation();

        $this->ensureEggIsReadyToHatch();

        $this->markEggAsHatched();
    }

    private function ensureIncubatorIsActive(): void
    {
        $availability = new GetIncubatorsAvailabilityAction()->handle($this->user);

        if (value_object_array_contains($availability['active'], $this->incubatorIndex)) {
            return;
        }

        throw new IncubatorNotActiveException($this->incubatorIndex);
    }

    private function loadIncubation(): void
    {
        $this->incubation = $this->user
            ->Incubations()
            ->active()
            ->where('incubator_index', $this->incubatorIndex->value)
            ->firstOrFail();
    }

    private function ensureEggIsReadyToHatch(): void
    {
        if ($this->incubation->getStatus() === IncubationStatus::READY_TO_HATCH) {
            return;
        }

        throw new IncubationNotReadyToHatchException;
    }

    private function markEggAsHatched(): void
    {
        $this->incubation->hatched_at = now();
        $this->incubation->save();
    }
}
