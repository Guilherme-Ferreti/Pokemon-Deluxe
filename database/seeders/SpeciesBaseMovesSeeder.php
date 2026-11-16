<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Shared\Models\Move;
use Shared\Models\Species;

final class SpeciesBaseMovesSeeder extends Seeder
{
    public function run(): void
    {
        $speciesIds = Species::pluck('id', 'slug')->toArray();
        $movesIds   = Move::query()
            ->where('is_z_move', false)
            ->where('is_dynamax_move', false)
            ->pluck('id', 'slug')
            ->toArray();

        /** @var array<string, list<string>> $baseMoves */
        $baseMoves = json_decode((string) file_get_contents(__DIR__ . '/files/species-base-moves.json'), true);

        $this->ensureEverySpeciesExists($baseMoves, $speciesIds);
        $this->ensureEverySpeciesHasEnoughDifferentMoves($baseMoves, $speciesIds);

        $records = [];

        foreach ($baseMoves as $speciesSlug => $moveSlugs) {
            foreach ($moveSlugs as $moveSlug) {
                throw_unless(
                    isset($movesIds[$moveSlug]),
                    RuntimeException::class,
                    "Move [{$moveSlug}] of species [{$speciesSlug}] does not exist or is a Z-Move or Dynamax move.",
                );

                $records[] = [
                    'species_id' => $speciesIds[$speciesSlug],
                    'move_id'    => $movesIds[$moveSlug],
                ];
            }
        }

        collect($records)
            ->chunk(1000)
            ->each(fn (Collection $chunk) => DB::table('species_base_move')->insert($chunk->toArray()));
    }

    /**
     * @param  array<string, list<string>>  $baseMoves
     * @param  array<string, string>  $speciesIds
     */
    private function ensureEverySpeciesExists(array $baseMoves, array $speciesIds): void
    {
        foreach (array_keys($baseMoves) as $speciesSlug) {
            throw_unless(
                isset($speciesIds[$speciesSlug]),
                RuntimeException::class,
                "Species [{$speciesSlug}] has base moves but does not exist.",
            );
        }
    }

    /**
     * @param  array<string, list<string>>  $baseMoves
     * @param  array<string, string>  $speciesIds
     */
    private function ensureEverySpeciesHasEnoughDifferentMoves(array $baseMoves, array $speciesIds): void
    {
        $moveCount = (int) config('game.pokemon.max_active_moves');

        foreach (array_keys($speciesIds) as $speciesSlug) {
            $moveSlugs          = $baseMoves[$speciesSlug] ?? [];
            $differentMoveSlugs = array_unique($moveSlugs);

            throw_unless(
                count($moveSlugs) === $moveCount && count($differentMoveSlugs) === $moveCount,
                RuntimeException::class,
                sprintf(
                    'Species [%s] must have exactly %d different base moves, [%d] given: [%s].',
                    $speciesSlug,
                    $moveCount,
                    count($differentMoveSlugs),
                    implode(', ', $moveSlugs),
                ),
            );
        }
    }
}
