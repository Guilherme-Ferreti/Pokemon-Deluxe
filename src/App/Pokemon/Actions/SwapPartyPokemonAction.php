<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Exceptions\PokemonAlreadyInPartyException;
use App\Pokemon\Exceptions\PokemonNotInPartyException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class SwapPartyPokemonAction
{
    private Pokemon $joining;

    private Pokemon $leaving;

    private User $owner;

    public function handle(Pokemon $joining, Pokemon $leaving): void
    {
        $this->joining = $joining;
        $this->leaving = $leaving;

        $this->ensureBothHaveTheSameOwner();

        DB::transaction(function (): void {
            $this->lockOwner();

            $this->joining->refresh();
            $this->leaving->refresh();

            $this->ensureNoBattleIsOngoing();

            $this->ensureJoiningIsNotInParty();

            $this->ensureLeavingIsInParty();

            $this->leaving->update(['in_party' => false]);
            $this->joining->update(['in_party' => true]);
        });
    }

    private function ensureBothHaveTheSameOwner(): void
    {
        throw_if($this->joining->user_id !== $this->leaving->user_id, InvalidArgumentException::class, 'Only Pokémon of the same owner can be swapped.');
    }

    private function lockOwner(): void
    {
        $this->owner = User::query()->whereKey($this->leaving->user_id)->lockForUpdate()->firstOrFail();
    }

    private function ensureNoBattleIsOngoing(): void
    {
        throw_if($this->owner->battles()->ongoing()->exists(), BattleAlreadyInProgressException::class);
    }

    private function ensureJoiningIsNotInParty(): void
    {
        throw_if($this->joining->in_party, fn () => new PokemonAlreadyInPartyException($this->joining->getDisplayName()));
    }

    private function ensureLeavingIsInParty(): void
    {
        throw_unless($this->leaving->in_party, fn () => new PokemonNotInPartyException($this->leaving->getDisplayName()));
    }
}
