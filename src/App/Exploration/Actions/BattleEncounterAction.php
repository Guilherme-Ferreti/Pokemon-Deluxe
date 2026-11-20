<?php

declare(strict_types=1);

namespace App\Exploration\Actions;

use App\Battle\Actions\StartWildBattleAction;
use App\Exploration\Exceptions\NoEncounterException;
use App\Exploration\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use LogicException;
use Shared\Models\Battle;
use Shared\Models\Encounter;
use Shared\Models\User;

final class BattleEncounterAction
{
    private User $user;

    private ?Encounter $encounter = null;

    public function handle(User $user): Battle
    {
        $this->user = $user;

        return Cache::withoutOverlapping(
            key: LockKeys::encounter($user->id),
            callback: fn () => $this->battleEncounter(),
        );
    }

    private function battleEncounter(): Battle
    {
        $this->loadEncounter();

        $this->ensureEncounterExists();

        return DB::transaction(function (): Battle {
            $battle = new StartWildBattleAction()->handle(
                $this->user,
                $this->existingEncounter()->toNewPokemon(),
                $this->existingEncounter()->area,
            );

            $this->existingEncounter()->delete();

            return $battle;
        });
    }

    private function loadEncounter(): void
    {
        $this->encounter = $this->user->encounter()->with('species')->first();
    }

    private function ensureEncounterExists(): void
    {
        throw_if(! $this->encounter instanceof Encounter, NoEncounterException::class);
    }

    private function existingEncounter(): Encounter
    {
        return $this->encounter ?? throw new LogicException('The encounter must be checked as existing first.');
    }
}
