<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->addPokemonToParty($user);
        $this->addPokemonToBox($user);
        $this->attachMovesToPokemon($user);
    }

    private function addPokemonToParty(User $user): void
    {
        $pokeballs = Pokeball::all();

        Species::query()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(fn (Species $species) => Pokemon::factory()
                ->for($user)
                ->for($user, 'originalUser')
                ->for($species)
                ->recycle($pokeballs)
                ->inParty()
                ->create()
            );
    }

    private function addPokemonToBox(User $user): void
    {
        $pokeballs = Pokeball::all();

        Species::query()
            ->inRandomOrder()
            ->get()
            ->map(fn (Species $species): array => [
                $this->prepareForInsert(Pokemon::factory()->for($user)->for($user, 'originalUser')->for($species)->recycle($pokeballs)->regular()->inBox()->make()),
                $this->prepareForInsert(Pokemon::factory()->for($user)->for($user, 'originalUser')->for($species)->recycle($pokeballs)->shiny()->inBox()->make()),
            ])
            ->flatten(1)
            ->chunk(1000)
            ->each(fn (Collection $chunk) => Pokemon::insert($chunk->toArray()));
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareForInsert(Pokemon $pokemon): array
    {
        $attributes = $pokemon->toArray();

        $attributes['id']          = $pokemon->newUniqueId();
        $attributes['captured_at'] = $pokemon->captured_at->format('Y-m-d H:i:s');
        $attributes['created_at']  = now();
        $attributes['updated_at']  = now();

        return $attributes;
    }

    private function attachMovesToPokemon(User $user): void
    {
        $user->pokemon()
            ->select('id', 'species_id')
            ->get()
            ->flatMap(fn (Pokemon $pokemon) => Arr::map($this->getRandomLearnableMoves($pokemon->species_id), fn (string $moveId) => [
                'pokemon_id' => $pokemon->id,
                'move_id'    => $moveId,
            ]))
            ->chunk(1000)
            ->each(fn (Collection $chunk) => DB::table('pokemon_moves')->insert($chunk->toArray()));
    }

    /**
     * @return array<string, array<string>>
     */
    private function getRandomLearnableMoves(string $speciesId): array
    {
        $allMoves = once(fn () => Species::query()
            ->with('learnableMoves:move_id,species_id')
            ->get()
            ->mapWithKeys(fn (Species $species) => [$species->id => $species->learnableMoves->pluck('move_id')->toArray()])
            ->toArray()
        );

        $moves = $allMoves[$speciesId];

        if (count($moves) >= 4) {
            return Arr::random($moves, 4);
        }

        while (count($moves) < 4) {
            $moves = [...$moves, ...$moves];
        }

        return Arr::take($moves, 4);
    }
}
