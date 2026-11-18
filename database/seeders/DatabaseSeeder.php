<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PokeballSeeder::class,
            EvolutionItemSeeder::class,
            MoveSeeder::class,
            SpeciesSeeder::class,
            SpeciesWildDataSeeder::class,
            SpeciesBaseMovesSeeder::class,
            UserSeeder::class,
        ]);
    }
}
