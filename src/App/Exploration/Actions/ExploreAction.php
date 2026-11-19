<?php

declare(strict_types=1);

namespace App\Exploration\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Exploration\Mechanics\EncounterRoller;
use App\Exploration\Queries\AreaSpeciesQuery;
use App\Exploration\Support\LockKeys;
use Illuminate\Support\Facades\Cache;
use Shared\Enums\Area;
use Shared\Models\Encounter;
use Shared\Models\User;

final class ExploreAction
{
    private User $user;

    private Area $area;

    public function handle(User $user, Area $area): Encounter
    {
        $this->user = $user;
        $this->area = $area;

        return Cache::withoutOverlapping(
            key: LockKeys::explore($user->id),
            callback: fn () => $this->explore(),
        );
    }

    private function explore(): Encounter
    {
        $this->ensureNoBattleIsOngoing();

        $wildPokemon = new EncounterRoller()->roll(new AreaSpeciesQuery()->handle($this->area)->get());

        return Encounter::query()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'area'       => $this->area,
                'species_id' => $wildPokemon->species->id,
                'level'      => $wildPokemon->level,
                'is_shiny'   => $wildPokemon->is_shiny,
                'gender'     => $wildPokemon->gender,
            ],
        );
    }

    private function ensureNoBattleIsOngoing(): void
    {
        throw_if($this->user->battles()->ongoing()->exists(), BattleAlreadyInProgressException::class);
    }
}
