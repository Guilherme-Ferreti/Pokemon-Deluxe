<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Exceptions\IncubatorAlreadyPurchasedException;
use App\Pokemon\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class PurchaseAction
{
    private User $user;

    private IncubatorIndex $incubatorIndex;

    public function handle(User $user, IncubatorIndex $incubatorIndex): void
    {
        $this->user           = $user;
        $this->incubatorIndex = $incubatorIndex;

        Cache::withoutOverlapping(
            key: LockKeys::incubatorPurchase($user->id),
            callback: fn () => $this->purchase()
        );
    }

    private function purchase(): void
    {
        $this->user->refresh();

        $this->ensureIncubatorHasNotBeenPurchased();

        $this->markIncubatorAsPurchased();
    }

    private function ensureIncubatorHasNotBeenPurchased(): void
    {
        $purchasedIndexes = $this->user->purchased_incubator_indexes;

        if (value_object_array_contains($purchasedIndexes, $this->incubatorIndex)) {
            throw new IncubatorAlreadyPurchasedException($this->incubatorIndex);
        }
    }

    private function markIncubatorAsPurchased(): void
    {
        $this->user->purchased_incubator_indexes = [
            ...$this->user->purchased_incubator_indexes,
            $this->incubatorIndex,
        ];

        $this->user->save();
    }
}
