<?php

declare(strict_types=1);

namespace App\Dex\CacheKeys;

final class DexCacheKeys
{
    public static function speciesCaptureStatistics(string $speciesId): string
    {
        return "dex:species:{$speciesId}:capture-statistics";
    }

    public static function userSpeciesCaptureStatistics(string $userId, string $speciesId): string
    {
        return "dex:user:{$userId}:species:{$speciesId}:capture-statistics";
    }
}
