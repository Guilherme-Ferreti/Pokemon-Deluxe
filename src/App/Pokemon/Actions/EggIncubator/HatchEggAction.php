<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\EggIncubator;

use App\Pokemon\CacheKeys\PokemonCacheKeys;
use App\Pokemon\Exceptions\EggIncubationNotReadyToHatchException;
use App\Pokemon\Exceptions\EggIncubatorNotActiveException;
use Illuminate\Support\Facades\Cache;
use Shared\Enums\EggIncubationStatus;
use Shared\Models\EggIncubation;
use Shared\Models\User;

final class HatchEggAction
{
    private User $user;

    private int $eggIncubatorIndex;

    private EggIncubation $eggIncubation;

    public function handle(User $user, int $eggIncubatorIndex): EggIncubation
    {
        $this->user              = $user;
        $this->eggIncubatorIndex = $eggIncubatorIndex;

        Cache::withoutOverlapping(
            key: PokemonCacheKeys::eggIncubatorHatchEggLock($this->user->id, $this->eggIncubatorIndex),
            callback: function (): void {
                $this->ensureEggIncubatorIsActive();

                $this->loadEggIncubation();

                $this->ensureEggIsReadyToHatch();

                $this->hatchEgg();
            });

        return $this->eggIncubation;
    }

    private function ensureEggIncubatorIsActive(): void
    {
        $availability = new GetEggIncubatorsAvailabilityAction()->handle($this->user);

        if (in_array($this->eggIncubatorIndex, $availability['active'])) {
            return;
        }

        throw new EggIncubatorNotActiveException($this->eggIncubatorIndex);
    }

    private function loadEggIncubation(): void
    {
        $this->eggIncubation = $this->user
            ->eggIncubations()
            ->active()
            ->where('egg_incubator_index', $this->eggIncubatorIndex)
            ->firstOrFail();
    }

    private function ensureEggIsReadyToHatch(): void
    {
        if ($this->eggIncubation->getStatus() === EggIncubationStatus::READY_TO_HATCH) {
            return;
        }

        throw new EggIncubationNotReadyToHatchException;
    }

    private function hatchEgg(): void
    {
        $this->eggIncubation->hatched_at = now();
        $this->eggIncubation->save();
    }
}
