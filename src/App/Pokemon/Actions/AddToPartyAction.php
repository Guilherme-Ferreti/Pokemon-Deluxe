<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Exceptions\PartyFullException;
use App\Pokemon\Exceptions\PokemonAlreadyInPartyException;
use Illuminate\Support\Facades\DB;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class AddToPartyAction
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

            $this->ensurePokemonIsNotInParty();

            $this->ensurePartyIsNotFull();

            $this->pokemon->update(['in_party' => true, 'party_position' => $this->nextPartyPosition()]);
        });
    }

    private function lockOwner(): void
    {
        $this->owner = User::query()->whereKey($this->pokemon->user_id)->lockForUpdate()->firstOrFail();
    }

    private function nextPartyPosition(): int
    {
        return (int) $this->owner->pokemon()->inParty()->max('party_position') + 1;
    }

    private function ensureNoBattleIsOngoing(): void
    {
        throw_if($this->owner->battles()->ongoing()->exists(), BattleAlreadyInProgressException::class);
    }

    private function ensurePokemonIsNotInParty(): void
    {
        throw_if($this->pokemon->in_party, fn () => new PokemonAlreadyInPartyException($this->pokemon->getDisplayName()));
    }

    private function ensurePartyIsNotFull(): void
    {
        throw_if($this->owner->pokemon()->inParty()->count() >= (int) config('game.user.max_party_size'), PartyFullException::class);
    }
}
