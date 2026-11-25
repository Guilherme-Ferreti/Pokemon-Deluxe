<?php

declare(strict_types=1);

namespace App\Shop\Support;

use Carbon\CarbonImmutable;

final class LockKeys
{
    public static function tmRotation(CarbonImmutable $date): string
    {
        return "shop::lock:tm-rotation:date-{$date->toDateString()}";
    }
}
