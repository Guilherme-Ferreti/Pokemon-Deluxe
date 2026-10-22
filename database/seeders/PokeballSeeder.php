<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shared\Models\Pokeball;

final class PokeballSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/files/pokeballs.json';

        $pokeballs = json_decode((string) file_get_contents($path), true);

        $records = [];

        foreach ($pokeballs as $pokeball) {
            $records[] = [
                'id'          => new Pokeball()->newUniqueId(),
                'slug'        => $pokeball['id'],
                'name'        => $pokeball['name'],
                'sprite'      => $pokeball['sprite'],
                'description' => $pokeball['description'],
                'type'        => $pokeball['type'],
                'catch_rate'  => $pokeball['catch_rate'],
            ];
        }

        Pokeball::insert($records);
    }
}
