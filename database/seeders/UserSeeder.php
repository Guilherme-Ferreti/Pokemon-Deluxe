<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shared\Models\Pokeball;
use Shared\Models\Pokedex;
use Shared\Models\Pokemon;
use Shared\Models\User;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $pokemonInParty = Pokedex::query()
            ->whereNotNull('sprites->front_default')
            ->inRandomOrder()
            ->take(3)
            ->get();

        $pokemonBox = Pokedex::query()
            ->whereNotNull('sprites->front_default')
            ->whereNotIn('id', $pokemonInParty->pluck('id')->toArray())
            ->inRandomOrder()
            ->get();

        $pokeballs = Pokeball::query()
            ->inRandomOrder()
            ->take(3)
            ->get();

        $pokemonInParty->each(fn (Pokedex $pokedex) => Pokemon::factory()->for($user)->for($pokedex)->recycle($pokeballs)->create(['in_party' => true]));

        $pokemonBox->each(fn (Pokedex $pokedex) => Pokemon::factory()->for($user)->for($pokedex)->recycle($pokeballs)->create(['in_party' => false]));
    }
}
