<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            // 'name'  => 'Test User',
            // 'email' => 'test@example.com',
        ]);

        $this->addPokemonToParty($user);
        $this->addPokemonToBox($user);
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
                ->create([
                    'moves_ids' => json_encode($this->getRandomLearnableMoves($species->id)),
                ])
            );
    }

    private function addPokemonToBox(User $user): void
    {
        $pokeballs = Pokeball::all();

        Species::query()
            ->inRandomOrder()
            ->get()
            ->map(function (Species $species) use ($user, $pokeballs): array {
                $regular = Pokemon::factory()->for($user)->for($user, 'originalUser')->for($species)->recycle($pokeballs)->regular()->inBox()->make()->toArray();
                $shiny   = Pokemon::factory()->for($user)->for($user, 'originalUser')->for($species)->recycle($pokeballs)->shiny()->inBox()->make()->toArray();

                $regular['id']         = new Pokemon()->newUniqueId();
                $regular['moves_ids']  = json_encode($this->getRandomLearnableMoves($species->id));
                $regular['created_at'] = now();
                $regular['updated_at'] = now();

                $shiny['id']         = new Pokemon()->newUniqueId();
                $shiny['moves_ids']  = json_encode($this->getRandomLearnableMoves($species->id));
                $shiny['created_at'] = now();
                $shiny['updated_at'] = now();

                return [$regular, $shiny];
            })
            ->flatten(1)
            ->chunk(1000)
            ->each(fn (Collection $chunk) => Pokemon::insert($chunk->toArray()));
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

        while (count($moves) <= 4) {
            $moves = [...$moves, ...$moves];
        }

        return Arr::take($moves, 4);
    }
}
