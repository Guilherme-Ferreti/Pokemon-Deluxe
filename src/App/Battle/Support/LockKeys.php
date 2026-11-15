<?php

declare(strict_types=1);

namespace App\Battle\Support;

final class LockKeys
{
    public static function startBattle(string $userId): string
    {
        return "battle::lock:start-battle:user-{$userId}";
    }

    public static function battleTurn(string $battleId): string
    {
        return "battle::lock:turn:battle-{$battleId}";
    }
}
