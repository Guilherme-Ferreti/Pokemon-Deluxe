<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Exceptions\IncubatorEggNotReadyToHatchException;
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
        $this->loadActiveIncubation();

        $this->ensureEggIsReadyToHatch();

        $this->markEggAsHatched();
    }

    private function loadActiveIncubation(): void
    {
        $incubator = new ListIncubatorsAction()->handle($this->user)->get($this->incubatorIndex->value);

        if (! $incubator?->isActive()) {
            throw new IncubatorNotActiveException($this->incubatorIndex);
        }

        $this->incubation = $incubator->incubation;
    }

    private function ensureEggIsReadyToHatch(): void
    {
        if ($this->incubation->getStatus() === IncubationStatus::READY_TO_HATCH) {
            return;
        }

        throw new IncubatorEggNotReadyToHatchException($this->incubatorIndex);
    }

    private function markEggAsHatched(): void
    {
        $this->incubation->hatched_at = now();
        $this->incubation->save();
    }
}
