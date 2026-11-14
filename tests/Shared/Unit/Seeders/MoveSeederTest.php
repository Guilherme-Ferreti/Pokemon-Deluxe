<?php

declare(strict_types=1);

use Database\Seeders\EvolutionItemSeeder;
use Database\Seeders\MoveSeeder;
use Database\Seeders\SpeciesBaseMovesSeeder;
use Database\Seeders\SpeciesSeeder;
use Shared\Models\Move;

use function Pest\Laravel\seed;

it('does not seed one-hit-KO moves', function () {
    seed([EvolutionItemSeeder::class, MoveSeeder::class, SpeciesSeeder::class, SpeciesBaseMovesSeeder::class]);

    expect(Move::query()->count())->toBeGreaterThan(0)
        ->and(Move::query()->where('category', 'ohko')->exists())->toBeFalse()
        ->and(Move::query()->whereIn('slug', ['fissure', 'guillotine', 'horn-drill', 'sheer-cold'])->exists())->toBeFalse();
});
