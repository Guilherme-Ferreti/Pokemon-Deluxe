<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shared\Models\Move;

/**
 * @phpstan-type SeederMove array{id:string, name:string, type:string, category:string, damage_class:string, power:?int, accuracy:int, pp:int, ailment:?string, ailment_chance:?int, flinch_chance:?int, min_hits:?int, max_hits:?int, is_zmove:bool, generation:int, slug:string, external_id:string}
 */
final class MoveSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/files/moves.json';

        $moves = json_decode((string) file_get_contents($path), true);

        $data = [];

        foreach ($moves as $move) {
            $data[] = $this->getMoveInfo($move);
        }

        collect($data)
            ->chunk(100)
            ->each(fn (Collection $chunk) => Move::insert($chunk->toArray()));
    }

    /**
     * @param  SeederMove  $move
     * @return array<string, mixed>
     */
    private function getMoveInfo(array $move): array
    {
        return [
            'id'             => new Move()->newUniqueId(),
            'name'           => $move['name'],
            'type'           => $move['type'],
            'category'       => $move['category'],
            'damage_class'   => $move['damage_class'],
            'power'          => $move['power'],
            'accuracy'       => $move['accuracy'],
            'pp'             => $move['pp'],
            'ailment'        => $move['ailment'],
            'ailment_chance' => $move['ailment_chance'],
            'flinch_chance'  => $move['flinch_chance'],
            'min_hits'       => $move['min_hits'],
            'max_hits'       => $move['max_hits'],
            'is_zmove'       => $move['is_zmove'],
            'generation'     => $move['generation'],
            'slug'           => Str::of($move['name'])->slug()->toString(),
            'external_id'    => $move['external_id'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }
}
