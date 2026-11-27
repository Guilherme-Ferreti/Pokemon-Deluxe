<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Exceptions\PartyCannotBeEmptyException;
use App\Pokemon\Exceptions\PokemonLockedException;
use Illuminate\Support\Facades\DB;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class ReleasePokemonAction
{
    private Pokemon $pokemon;

    private User $owner;

    public function handle(Pokemon $pokemon): void
    {
        $this->pokemon = $pokemon;

        DB::transaction(function (): void {
            $this->lockOwner();

            $this->pokemon->refresh();

            $this->ensureNoBattleIsOngoing();

            $this->ensurePokemonIsNotLocked();

            $this->ensurePartyKeepsAPokemon();

            $this->pokemon->delete();

            $this->closePartyGapAt($this->pokemon->party_position);
        });
    }

    private function lockOwner(): void
    {
        $this->owner = User::query()->whereKey($this->pokemon->user_id)->lockForUpdate()->firstOrFail();
    }

    private function closePartyGapAt(?int $partyPosition): void
    {
        if ($partyPosition === null) {
            return;
        }

        $this->owner->pokemon()->inParty()->where('party_position', '>', $partyPosition)->decrement('party_position');
    }

    private function ensureNoBattleIsOngoing(): void
    {
        throw_if($this->owner->battles()->ongoing()->exists(), BattleAlreadyInProgressException::class);
    }

    private function ensurePokemonIsNotLocked(): void
    {
        throw_if($this->pokemon->is_locked, fn () => new PokemonLockedException($this->pokemon->getDisplayName()));
    }

    private function ensurePartyKeepsAPokemon(): void
    {
        throw_if($this->pokemon->in_party && $this->owner->pokemon()->inParty()->count() <= 1, PartyCannotBeEmptyException::class);
    }
}
