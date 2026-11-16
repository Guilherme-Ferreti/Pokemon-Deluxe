<?php

declare(strict_types=1);

namespace App\Battle\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;

final readonly class BattleEnded implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public string $battleId,
        public string $userId,
        public BattleType $type,
        public BattleStatus $status,
        public bool $isWin,
    ) {}
}
