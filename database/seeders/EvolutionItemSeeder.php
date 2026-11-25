<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shared\Models\EvolutionItem;

final class EvolutionItemSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/files/evolution-items.json';

        $items = json_decode((string) file_get_contents($path), true);

        $records = [];

        foreach ($items as $item) {
            $records[] = [
                'id'          => new EvolutionItem()->newUniqueId(),
                'slug'        => $item['id'],
                'name'        => $item['name'],
                'sprite'      => $item['sprite'],
                'description' => $item['description'],
                'price'       => $item['price'],
            ];
        }

        EvolutionItem::insert($records);
    }
}
