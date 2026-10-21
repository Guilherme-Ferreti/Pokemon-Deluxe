<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\SpeciesLearnableMove;

final class SpeciesLearnableMovesSeeder extends Seeder
{
    /** @var array<string, string> */
    private array $speciesIds = [];

    /** @var array<string, string> */
    private array $movesIds = [];

    public function run(): void
    {
        $this->speciesIds = Species::pluck('id', 'slug')->toArray();
        $this->movesIds   = Move::pluck('id', 'slug')->toArray();

        $this->insertMovesFromFiles();
    }

    private function insertMovesFromFiles(): void
    {
        $records = [];

        foreach ($this->getFilesList() as $path) {
            $moves = json_decode((string) file_get_contents($path), true);

            foreach ($moves as $speciesId => $moves) {
                foreach ($moves as $moveId => $move) {
                    $records[] = [
                        'id'           => new SpeciesLearnableMove()->newUniqueId(),
                        'species_id'   => $this->speciesIds[$speciesId],
                        'move_id'      => $this->movesIds[$moveId],
                        'level_learn'  => $move['level_learn'],
                        'is_base_move' => $move['is_base_move'],
                    ];
                }
            }
        }

        collect($records)
            ->chunk(10000)
            ->each(fn (Collection $chunk) => SpeciesLearnableMove::insert($chunk->toArray()));
    }

    /**
     * @return list<string>
     */
    private function getFilesList(): array
    {
        return [
            __DIR__ . '/files/species-learnable-moves/gen-I.json',
            __DIR__ . '/files/species-learnable-moves/gen-II.json',
            __DIR__ . '/files/species-learnable-moves/gen-III.json',
            __DIR__ . '/files/species-learnable-moves/gen-IV.json',
            __DIR__ . '/files/species-learnable-moves/gen-V.json',
            __DIR__ . '/files/species-learnable-moves/gen-VI.json',
            __DIR__ . '/files/species-learnable-moves/gen-VII.json',
            __DIR__ . '/files/species-learnable-moves/gen-VIII.json',
            __DIR__ . '/files/species-learnable-moves/gen-IX.json',
        ];
    }
}
