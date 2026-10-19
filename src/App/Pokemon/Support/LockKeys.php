<?php

declare(strict_types=1);

namespace App\Pokemon\Support;

use Shared\ValueObjects\EggIncubatorIndex;

final class LockKeys
{
    public static function eggIncubatorPurchase(string $userId): string
    {
        return "pokemon:lock:egg-incubator-purchase:user-{$userId}";
    }

    public static function eggIncubatorPlaceEgg(string $userId, EggIncubatorIndex $eggIncubatorIndex): string
    {
        return "pokemon:lock:egg-incubator-place-egg:user-{$userId}:egg-incubator-{$eggIncubatorIndex->value}";
    }

    public static function eggIncubatorHatchEgg(string $userId, EggIncubatorIndex $eggIncubatorIndex): string
    {
        return "pokemon:lock:egg-incubator-hatch-egg:user-{$userId}:egg-incubator-{$eggIncubatorIndex->value}";
    }
}
