<?php

declare(strict_types=1);

namespace App\Pokemon\Actions\Incubator;

use App\Pokemon\Support\Incubator;
use Illuminate\Support\Collection;
use Shared\Helpers\AppHelper;
use Shared\Models\Incubation;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

final class ListIncubatorsAction
{
    /**
     * @return Collection<int, Incubator>
     */
    public function handle(User $user): Collection
    {
        $purchasedIndexes = $user->purchased_incubator_indexes;

        $activeIncubations = $user
            ->incubations()
            ->active()
            ->get()
            ->keyBy(fn (Incubation $incubation) => $incubation->incubator_index->value);

        return collect(AppHelper::allIncubatorIndexes())
            ->mapWithKeys(fn (IncubatorIndex $index): array => [
                $index->value => new Incubator(
                    index: $index,
                    isPurchased: value_object_array_contains($purchasedIndexes, $index),
                    incubation: $activeIncubations->get($index->value),
                ),
            ]);
    }
}
