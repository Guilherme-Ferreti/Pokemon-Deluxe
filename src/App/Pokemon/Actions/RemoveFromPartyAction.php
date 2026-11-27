<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Pokemon\Exceptions\PartyCannotBeEmptyException;
use App\Pokemon\Exceptions\PokemonNotInPartyException;
use Illuminate\Support\Facades\DB;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class RemoveFromPartyAction
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

            $this->ensurePokemonIsInParty();

            $this->ensurePartyKeepsAPokemon();

            $partyPosition = $this->pokemon->party_position;

            $this->pokemon->update(['in_party' => false, 'party_position' => null]);

            $this->closePartyGapAt($partyPosition);
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

    private function ensurePokemonIsInParty(): void
    {
        throw_unless($this->pokemon->in_party, fn () => new PokemonNotInPartyException($this->pokemon->getDisplayName()));
    }

    private function ensurePartyKeepsAPokemon(): void
    {
        throw_if($this->owner->pokemon()->inParty()->count() <= 1, PartyCannotBeEmptyException::class);
    }
}
