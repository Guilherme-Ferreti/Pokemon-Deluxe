<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\EggIncubator;

use App\Pokemon\Exceptions\EggIncubatorAlreadyPurchasedException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

final class PurchaseAction
{
    private User $user;

    private EggIncubatorIndex $eggIncubatorIndex;

    public function handle(User $user, EggIncubatorIndex $eggIncubatorIndex): void
    {
        $this->user              = $user;
        $this->eggIncubatorIndex = $eggIncubatorIndex;

        Cache::withoutOverlapping(
            key: LockKeys::eggIncubatorPurchase($user->id),
            callback: fn () => $this->purchase()
        );
    }

    private function purchase(): void
    {
        $this->user = $this->user->fresh() ?? $this->user;

        $this->ensureEggIncubatorHasNotBeenPurchased();

        $this->markEggIncubatorAsPurchased();
    }

    private function ensureEggIncubatorHasNotBeenPurchased(): void
    {
        $purchasedIndexes = $this->user->purchased_egg_incubator_indexes;

        if (value_object_array_contains($purchasedIndexes, $this->eggIncubatorIndex)) {
            throw new EggIncubatorAlreadyPurchasedException($this->eggIncubatorIndex);
        }
    }

    private function markEggIncubatorAsPurchased(): void
    {
        $this->user->purchased_egg_incubator_indexes = [
            ...$this->user->purchased_egg_incubator_indexes,
            $this->eggIncubatorIndex,
        ];

        $this->user->save();
    }
}
