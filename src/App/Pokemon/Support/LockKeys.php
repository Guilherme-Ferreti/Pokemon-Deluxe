<?php

declare(strict_types=1);

namespace App\Pokemon\Support;

final class LockKeys
{
    public static function eggIncubatorPlaceEgg(string $userId, int $eggIncubatorIndex): string
    {
        return "pokemon:lock:egg-incubator-place-egg:user-{$userId}:egg-incubator-{$eggIncubatorIndex}";
    }

    public static function eggIncubatorHatchEgg(string $userId, int $eggIncubatorIndex): string
    {
        return "pokemon:lock:egg-incubator-hatch-egg:user-{$userId}:egg-incubator-{$eggIncubatorIndex}";
    }
}
