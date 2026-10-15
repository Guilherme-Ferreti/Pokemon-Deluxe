<?php

declare(strict_types=1);

namespace App\Dex\Support;

final class CacheKeys
{
    public static function speciesCaptureStatistics(string $speciesId): string
    {
        return "dex:query:species-capture-statistics:species-{$speciesId}";
    }

    public static function userSpeciesCaptureStatistics(string $userId, string $speciesId): string
    {
        return "dex:query:user-species-capture-statistics:user-{$userId}:species-{$speciesId}";
    }

    public static function speciesEvolutionChain(string $speciesId): string
    {
        return "dex:query:species-evolution-chain:species-{$speciesId}";
    }

    public static function speciesLearnableMoves(string $speciesId): string
    {
        return "dex:query:species-learnable-moves:species-{$speciesId}";
    }
}
