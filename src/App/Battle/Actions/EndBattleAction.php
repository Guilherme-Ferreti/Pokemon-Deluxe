<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Events\BattleEnded;
use InvalidArgumentException;
use LogicException;
use Shared\Enums\BattleStatus;
use Shared\Models\Battle;

final class EndBattleAction
{
    private Battle $battle;

    private BattleStatus $status;

    public function handle(Battle $battle, BattleStatus $status): Battle
    {
        $this->battle = $battle;
        $this->status = $status;

        $this->ensureStatusIsAnEnding();
        $this->ensureBattleIsOngoing();

        $this->endBattle();

        return $this->battle;
    }

    private function ensureStatusIsAnEnding(): void
    {
        throw_if($this->status === BattleStatus::ONGOING, InvalidArgumentException::class, 'A battle cannot end as ongoing.');
    }

    private function ensureBattleIsOngoing(): void
    {
        throw_if($this->battle->status !== BattleStatus::ONGOING, LogicException::class, 'The battle has already ended.');
    }

    private function endBattle(): void
    {
        $this->battle->update(['status' => $this->status]);

        BattleEnded::dispatch(
            $this->battle->id,
            $this->battle->user_id,
            $this->battle->type,
            $this->status,
            in_array($this->status, [BattleStatus::WON, BattleStatus::CAUGHT], true),
        );
    }
}
