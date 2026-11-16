<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Exceptions\BattleAlreadyEndedException;
use App\Battle\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Shared\Enums\BattleStatus;
use Shared\Models\Battle;

final class ForfeitBattleAction
{
    private Battle $battle;

    public function handle(Battle $battle): Battle
    {
        $this->battle = $battle;

        return Cache::withoutOverlapping(
            key: LockKeys::battle($battle->id),
            callback: fn () => $this->forfeit(),
        );
    }

    private function forfeit(): Battle
    {
        $this->battle->refresh();

        throw_if($this->battle->status !== BattleStatus::ONGOING, BattleAlreadyEndedException::class);

        DB::transaction(function (): void {
            $this->battle->update(['state' => $this->battle->state->withLogLines(['You ran away!'])]);

            new EndBattleAction()->handle($this->battle, BattleStatus::FORFEITED);
        });

        return $this->battle;
    }
}
