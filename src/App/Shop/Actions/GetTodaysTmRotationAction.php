<?php

declare(strict_types=1);

namespace App\Shop\Actions;

use App\Shop\Mechanics\TmPriceCalculator;
use App\Shop\Support\LockKeys;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Shared\Helpers\RandomHelper;
use Shared\Models\Move;
use Shared\Models\TmRotation;

final class GetTodaysTmRotationAction
{
    private CarbonImmutable $today;

    public function handle(): TmRotation
    {
        $this->today = CarbonImmutable::today();

        return $this->findTodaysRotation() ?? Cache::withoutOverlapping(
            key: LockKeys::tmRotation($this->today),
            callback: fn () => $this->findOrCreateTodaysRotation(),
        );
    }

    private function findTodaysRotation(): ?TmRotation
    {
        return TmRotation::query()->whereDate('date', $this->today)->first();
    }

    private function findOrCreateTodaysRotation(): TmRotation
    {
        return $this->findTodaysRotation() ?? $this->createTodaysRotation();
    }

    private function createTodaysRotation(): TmRotation
    {
        return TmRotation::query()->create([
            'date'     => $this->today,
            'move_ids' => RandomHelper::pickMany($this->sellableTmIds(), (int) config('game.shop.daily_tm_count')),
        ]);
    }

    /**
     * @return non-empty-list<string>
     */
    private function sellableTmIds(): array
    {
        $tmPriceCalculator = new TmPriceCalculator;

        /** @var non-empty-list<string> */
        return Move::query()
            ->get()
            ->filter(fn (Move $move) => $tmPriceCalculator->price($move) !== null)
            ->modelKeys();
    }
}
