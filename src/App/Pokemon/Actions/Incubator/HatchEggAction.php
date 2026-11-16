<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Exceptions\IncubatorEggNotReadyToHatchException;
use App\Pokemon\Exceptions\IncubatorNotActiveException;
use App\Pokemon\Support\Incubator;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use LogicException;
use Shared\Enums\IncubationStatus;
use Shared\Models\Incubation;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class HatchEggAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    private ?Incubator $incubator = null;

    public function handle(User $user, IncubatorIndex $incubatorIndex): Incubation
    {
        $this->user           = $user;
        $this->incubatorIndex = $incubatorIndex;

        Cache::withoutOverlapping(
            key: LockKeys::incubatorHatchEgg($this->user->id, $this->incubatorIndex),
            callback: fn () => $this->hatchEgg()
        );

        return $this->activeIncubation();
    }

    private function hatchEgg(): void
    {
        $this->loadIncubator();

        $this->ensureIncubatorIsActive();
        $this->ensureEggIsReadyToHatch();

        $this->markEggAsHatched();
    }

    private function loadIncubator(): void
    {
        $this->incubator = new ListIncubatorsAction()->handle($this->user)->get($this->incubatorIndex->value);
    }

    private function ensureIncubatorIsActive(): void
    {
        throw_unless($this->incubator?->isActive(), new IncubatorNotActiveException($this->incubatorIndex));
    }

    private function ensureEggIsReadyToHatch(): void
    {
        throw_unless(
            $this->activeIncubation()->getStatus() === IncubationStatus::READY_TO_HATCH,
            new IncubatorEggNotReadyToHatchException($this->incubatorIndex),
        );
    }

    private function activeIncubation(): Incubation
    {
        return $this->incubator->incubation ?? throw new LogicException('The incubator must be checked as active first.');
    }

    private function markEggAsHatched(): void
    {
        $incubation = $this->activeIncubation();

        $incubation->hatched_at = now();
        $incubation->save();
    }
}
