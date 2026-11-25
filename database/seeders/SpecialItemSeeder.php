<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shared\Models\SpecialItem;

final class SpecialItemSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/files/special-items.json';

        $items = json_decode((string) file_get_contents($path), true);

        $records = [];

        foreach ($items as $item) {
            $records[] = [
                'id'          => new SpecialItem()->newUniqueId(),
                'slug'        => $item['id'],
                'name'        => $item['name'],
                'sprite'      => $item['sprite'],
                'description' => $item['description'],
                'category'    => $item['category'],
            ];
        }

        SpecialItem::insert($records);
    }
}
