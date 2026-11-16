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
    public function handle(Battle $battle, BattleStatus $status): Battle
    {
        throw_if($status === BattleStatus::ONGOING, InvalidArgumentException::class, 'A battle cannot end as ongoing.');

        throw_if($battle->status !== BattleStatus::ONGOING, LogicException::class, 'The battle has already ended.');

        $battle->update(['status' => $status]);

        BattleEnded::dispatch(
            $battle->id,
            $battle->user_id,
            $battle->type,
            $status,
            in_array($status, [BattleStatus::WON, BattleStatus::CAUGHT], true),
        );

        return $battle;
    }
}
