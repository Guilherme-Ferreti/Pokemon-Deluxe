<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Exceptions\BoxFullException;
use App\Pokemon\Mechanics\ExperienceCalculator;
use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Support\Facades\DB;
use Shared\Exceptions\GameDataException;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class CreatePokemonAction
{
    private User $owner;

    private NewPokemon $newPokemon;

    private Pokeball $pokeball;

    public function handle(User $owner, NewPokemon $newPokemon, Pokeball $pokeball): Pokemon
    {
        $this->owner      = $owner;
        $this->newPokemon = $newPokemon;
        $this->pokeball   = $pokeball;

        return DB::transaction(function () {
            $this->lockOwner();

            $this->ensureOwnersBoxIsNotFull();

            $pokemon = $this->createPokemon();

            $this->addBaseMoves($pokemon);

            return $pokemon;
        });
    }

    private function lockOwner(): void
    {
        User::query()->whereKey($this->owner->id)->lockForUpdate()->firstOrFail();
    }

    private function ensureOwnersBoxIsNotFull(): void
    {
        $pokemonCount = $this->owner->pokemon()->count();

        throw_if($pokemonCount >= config('game.user.max_box_size'), BoxFullException::class);
    }

    private function createPokemon(): Pokemon
    {
        return Pokemon::create([
            'user_id'          => $this->owner->id,
            'original_user_id' => $this->owner->id,
            'species_id'       => $this->newPokemon->species->id,
            'level'            => $this->newPokemon->level,
            'experience'       => new ExperienceCalculator()->experienceForLevel($this->newPokemon->level),
            'boost'            => 0,
            'in_party'         => false,
            'is_shiny'         => $this->newPokemon->is_shiny,
            'gender'           => $this->newPokemon->gender,
            'pokeball_id'      => $this->pokeball->id,
            'caught_at'        => now(),
        ]);
    }

    private function addBaseMoves(Pokemon $pokemon): void
    {
        $moveIds = $this->newPokemon->species->baseMoves()->take((int) config('game.pokemon.max_active_moves'))->pluck('moves.id');

        if ($moveIds->isEmpty()) {
            report(GameDataException::speciesWithoutBaseMoves($this->newPokemon->species));

            $moveIds = collect([$this->defaultBaseMove()->id]);
        }

        $pokemon->knownMoves()->attach(
            $moveIds->mapWithKeys(fn (string $moveId, int $index) => [$moveId => ['slot' => $index + 1]])->all(),
        );
    }

    private function defaultBaseMove(): Move
    {
        return Move::query()
            ->where('slug', config('game.pokemon.default_base_move'))
            ->firstOrFail();
    }
}
