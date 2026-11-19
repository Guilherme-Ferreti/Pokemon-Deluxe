<?php

declare(strict_types=1);

namespace App\Exploration\Support;

final class LockKeys
{
    public static function explore(string $userId): string
    {
        return "exploration::lock:explore:user-{$userId}";
    }
}
