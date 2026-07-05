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

        $data = [];

        foreach ($pokeballs as $pokeball) {
            $data[] = [
                'id'           => new Pokeball()->newUniqueId(),
                'name'         => $pokeball['name'],
                'sprite'       => $pokeball['sprite'],
                'description'  => $pokeball['description'],
                'type'         => $pokeball['type'],
                'capture_rate' => $pokeball['capture_rate'],
                'internal_id'  => $pokeball['id'],
                'external_id'  => $pokeball['external_id'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        Pokeball::insert($data);
    }
}
