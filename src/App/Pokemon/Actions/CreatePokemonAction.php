<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Exceptions\BoxFullException;
use App\Pokemon\Services\ExperienceCalculator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\NewPokemon;

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

        $this->ensureOwnersBoxIsNotFull();

        return DB::transaction(function () {
            $pokemon = $this->createPokemon();

            $this->addBaseMoves($pokemon);

            return $pokemon;
        });
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
            'in_party'         => false,
            'is_shiny'         => $this->newPokemon->is_shiny,
            'gender'           => $this->newPokemon->gender,
            'pokeball_id'      => $this->pokeball->id,
            'caught_at'        => now(),
        ]);
    }

    private function addBaseMoves(Pokemon $pokemon): void
    {
        $moveIds = $this->newPokemon->species->learnableMoves()->baseMove()->take(4)->pluck('move_id')->toArray();

        while (count($moveIds) < 4) {
            $moveIds = [...$moveIds, ...$moveIds];
        }

        $pokemon->moves()->attach(Arr::take($moveIds, 4));
    }
}
