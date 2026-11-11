<?php

declare(strict_types=1);

namespace App\Dex\Support;

final class CacheKeys
{
    public static function speciesCatchStatistics(string $speciesId): string
    {
        return "dex::query:species-catch-statistics:species-{$speciesId}";
    }

    public static function userSpeciesCatchStatistics(string $userId, string $speciesId): string
    {
        return "dex::query:user-species-catch-statistics:user-{$userId}:species-{$speciesId}";
    }

    public static function speciesEvolutionChain(string $speciesId): string
    {
        return "dex::query:species-evolution-chain:species-{$speciesId}";
    }

    public static function speciesBaseMoves(string $speciesId): string
    {
        return "dex::query:species-base-moves:species-{$speciesId}";
    }
}
