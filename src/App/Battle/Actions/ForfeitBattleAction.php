<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Exceptions\BattleAlreadyEndedException;
use App\Battle\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\ValueObjects\BattleLogEntry;

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

        $this->ensureBattleCanBeForfeited();

        DB::transaction(function (): void {
            $this->battle->update([
                'state' => $this->battle->state->withLogEntries([
                    new BattleLogEntry($this->battle->state->endTurn(), BattleLogKind::INFO, $this->endingText()),
                ]),
            ]);

            new EndBattleAction()->handle($this->battle, BattleStatus::FORFEITED);
        });

        return $this->battle;
    }

    private function endingText(): string
    {
        return $this->battle->type === BattleType::WILD ? 'You got away safely!' : 'You forfeited the battle.';
    }

    private function ensureBattleCanBeForfeited(): void
    {
        throw_if($this->battle->status !== BattleStatus::ONGOING, BattleAlreadyEndedException::class);
    }
}
