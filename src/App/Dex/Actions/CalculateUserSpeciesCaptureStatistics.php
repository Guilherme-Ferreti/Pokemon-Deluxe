<?php

declare(strict_types=1);

namespace App\Dex\Actions;

use App\Dex\CacheKeys\DexCacheKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Models\Species;
use Shared\Models\User;

/**
 * @phpstan-type Statistics array{regular: int, shiny:int}
 */
final class CalculateUserSpeciesCaptureStatistics
{
    /**
     * @return Statistics
     */
    public function handle(User $user, Species $species): array
    {
        return Cache::remember(
            key: DexCacheKeys::userSpeciesCaptureStatistics($user->id, $species->id),
            ttl: now()->endOfWeek(),
            callback: fn () => $this->calculate($user, $species)
        );
    }

    /**
     * @return Statistics
     */
    private function calculate(User $user, Species $species): array
    {
        return [
            'regular' => $species->pokemon()->regular()->whereBelongsTo($user)->count(),
            'shiny'   => $species->pokemon()->shiny()->whereBelongsTo($user)->count(),
        ];
    }
}
