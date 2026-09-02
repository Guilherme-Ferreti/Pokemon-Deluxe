<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
    }

    private function addPokemonToParty(User $user): void
    {
        $pokeballs = Pokeball::all();

        Species::query()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(fn (Species $species) => Pokemon::factory()->for($user)->for($species)->recycle($pokeballs)->inParty()->create());
    }

    private function addPokemonToBox(User $user): void
    {
        $pokeballs = Pokeball::all();

        $rows = Species::query()
            ->inRandomOrder()
            ->get()
            ->map(function (Species $species) use ($user, $pokeballs): array {
                $regular = Pokemon::factory()->for($user)->for($species)->recycle($pokeballs)->regular()->inBox()->make()->toArray();
                $shiny   = Pokemon::factory()->for($user)->for($species)->recycle($pokeballs)->shiny()->inBox()->make()->toArray();

                $regular['id'] = new Pokemon()->newUniqueId();
                $shiny['id']   = new Pokemon()->newUniqueId();

                $regular['moves_ids'] = json_encode($regular['moves_ids']);
                $shiny['moves_ids']   = json_encode($shiny['moves_ids']);

                return [$regular, $shiny];
            })
            ->flatten(1)
            ->toArray();

        Pokemon::insert($rows);
    }
}
