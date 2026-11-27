<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class ReorderPartyAction
{
    private User $owner;

    /** @var list<string> */
    private array $pokemonIds;

    /** @var list<string> */
    private array $partyPokemonIds = [];

    /**
     * @param  list<string>  $pokemonIds
     */
    public function handle(User $user, array $pokemonIds): void
    {
        $this->owner      = $user;
        $this->pokemonIds = $pokemonIds;

        DB::transaction(function (): void {
            $this->lockOwner();

            $this->loadPartyPokemonIds();

            $this->ensureNoBattleIsOngoing();

            $this->ensureListIsExactlyTheParty();

            $this->savePartyPositions();
        });
    }

    private function lockOwner(): void
    {
        User::query()->whereKey($this->owner->id)->lockForUpdate()->firstOrFail();
    }

    private function loadPartyPokemonIds(): void
    {
        $this->partyPokemonIds = array_values($this->owner->pokemon()->inParty()->pluck('id')->all());
    }

    private function ensureNoBattleIsOngoing(): void
    {
        throw_if($this->owner->battles()->ongoing()->exists(), BattleAlreadyInProgressException::class);
    }

    private function ensureListIsExactlyTheParty(): void
    {
        $sortedPokemonIds      = $this->pokemonIds;
        $sortedPartyPokemonIds = $this->partyPokemonIds;

        sort($sortedPokemonIds);
        sort($sortedPartyPokemonIds);

        throw_if($sortedPokemonIds !== $sortedPartyPokemonIds, InvalidArgumentException::class, 'The new order must list every party Pokémon exactly once.');
    }

    private function savePartyPositions(): void
    {
        foreach ($this->pokemonIds as $index => $pokemonId) {
            Pokemon::query()->whereKey($pokemonId)->update(['party_position' => $index + 1]);
        }
    }
}
