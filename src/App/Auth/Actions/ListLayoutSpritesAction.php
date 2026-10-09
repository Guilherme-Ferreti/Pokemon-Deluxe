<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Lottery;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesSprites;

final class ListLayoutSpritesAction
{
    /**
     * @return array<mixed>
     */
    public function handle(): array
    {
        return Cache::remember(
            key: 'auth:layout:sprites',
            ttl: now()->addDay(),
            callback: fn () => $this->listSprites()
        );
    }

    /**
     * @return array<mixed>
     */
    private function listSprites(): array
    {
        return Species::query()
            ->whereNotNull('sprites->front_default')
            ->whereNotNull('sprites->front_shiny')
            ->inRandomOrder()
            ->limit(49)
            ->pluck('sprites')
            ->map(fn (SpeciesSprites $sprites) => Lottery::odds(1, 100)
                ->winner(fn () => $sprites->front_shiny)
                ->loser(fn () => $sprites->front_default)
                ->choose()
            )
            ->toArray();
    }
}
