<?php

declare(strict_types=1);

namespace App\Dex\Actions;

use App\Dex\CacheKeys\DexCacheKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Models\Species;

/**
 * @phpstan-type Statistics array{regular: int, shiny:int}
 */
final class CalculateSpeciesCaptureStatistics
{
    /**
     * @return Statistics
     */
    public function handle(Species $species): array
    {
        return Cache::remember(
            key: DexCacheKeys::speciesCaptureStatistics($species->id),
            ttl: now()->endOfDay(),
            callback: fn () => $this->calculate($species)
        );
    }

    /**
     * @return Statistics
     */
    private function calculate(Species $species): array
    {
        return [
            'regular' => $species->pokemon()->regular()->count(),
            'shiny'   => $species->pokemon()->shiny()->count(),
        ];
    }
}
