<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Shared\Exceptions\GameDataException;
use Shared\Models\Species;

it('reports species without base moves to the game data log channel', function () {
    $species   = Species::factory()->create();
    $exception = GameDataException::speciesWithoutBaseMoves($species);

    Log::shouldReceive('channel')->once()->with('game-data')->andReturnSelf();
    Log::shouldReceive('error')->once()->with(
        "Species [{$species->slug}] has no base moves.",
        [
            'species_id'   => $species->id,
            'species_slug' => $species->slug,
            'exception'    => $exception,
        ],
    );

    report($exception);
});
