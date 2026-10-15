<?php

declare(strict_types=1);

namespace App\Pokemon\CacheKeys;

final class PokemonCacheKeys
{
    public static function eggIncubatorPlaceEggLock(string $userId, int $eggIncubatorIndex): string
    {
        return "pokemon:user:{$userId}:egg-incubator:{$eggIncubatorIndex}:place-egg-lock";
    }

    public static function eggIncubatorHatchEggLock(string $userId, int $eggIncubatorIndex): string
    {
        return "pokemon:user:{$userId}:egg-incubator:{$eggIncubatorIndex}:hatch-egg-lock";
    }
}
