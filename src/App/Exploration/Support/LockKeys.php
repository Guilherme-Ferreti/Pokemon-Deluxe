<?php

declare(strict_types=1);

namespace App\Exploration\Support;

final class LockKeys
{
    public static function encounter(string $userId): string
    {
        return "exploration::lock:encounter:user-{$userId}";
    }
}
