<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Inventory\Exceptions\InsufficientInventoryException;
use App\Pokemon\Exceptions\MaxBoostReachedException;
use App\Pokemon\Mechanics\BoostStoneRoller;
use App\Pokemon\Support\LockKeys;
use App\Pokemon\ValueObjects\BoostResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Models\InventoryItem;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;

final class UseBoostStonesAction
{
    private Pokemon $pokemon;

    private int $maxStones;

    private ?int $targetBoost;

    private SpecialItem $boostStone;

    private int $ownedStones = 0;

    public function handle(Pokemon $pokemon, int $maxStones, ?int $targetBoost = null): BoostResult
    {
        $this->pokemon     = $pokemon;
        $this->maxStones   = $maxStones;
        $this->targetBoost = $targetBoost;

        $this->ensureMaxStonesIsPositive();
        $this->ensureTargetBoostIsInRange();

        return Cache::withoutOverlapping(
            key: LockKeys::pokemonBoost($pokemon->id),
            callback: fn () => DB::transaction(fn () => $this->useBoostStones()),
        );
    }

    private function useBoostStones(): BoostResult
    {
        $this->pokemon->refresh();

        $this->loadBoostStone();
        $this->loadOwnedStones();

        $this->ensureBoostIsBelowMax();
        $this->ensureStonesAreOwned();

        $boostBefore = $this->pokemon->boost;
        $boost       = $boostBefore;
        $stonesUsed  = 0;
        $roller      = new BoostStoneRoller;

        while ($this->canUseAnotherStone($stonesUsed, $boost)) {
            if ($roller->roll($boost)) {
                $boost++;
            }

            $stonesUsed++;
        }

        if ($stonesUsed > 0) {
            new RemoveInventoryItemAction()->handle($this->pokemon->user, $this->boostStone, $stonesUsed);

            $this->pokemon->update(['boost' => $boost]);
        }

        return new BoostResult(
            stonesUsed: $stonesUsed,
            successes: $boost - $boostBefore,
            failures: $stonesUsed - ($boost - $boostBefore),
            boostBefore: $boostBefore,
            boostAfter: $boost,
        );
    }

    private function ensureMaxStonesIsPositive(): void
    {
        throw_if($this->maxStones < 1, InvalidArgumentException::class, "At least 1 stone must be used, [{$this->maxStones}] given.");
    }

    private function ensureTargetBoostIsInRange(): void
    {
        throw_if(
            $this->targetBoost !== null && ($this->targetBoost < 1 || $this->targetBoost > $this->maxBoost()),
            InvalidArgumentException::class,
            "The target boost must be between 1 and {$this->maxBoost()}, [{$this->targetBoost}] given.",
        );
    }

    private function loadBoostStone(): void
    {
        $this->boostStone = SpecialItem::query()->where('slug', 'boost-stone')->firstOrFail();
    }

    private function loadOwnedStones(): void
    {
        $this->ownedStones = (int) InventoryItem::query()
            ->where('user_id', $this->pokemon->user_id)
            ->forItem($this->boostStone)
            ->value('quantity');
    }

    private function ensureBoostIsBelowMax(): void
    {
        throw_if(
            $this->pokemon->boost >= $this->maxBoost(),
            fn () => new MaxBoostReachedException($this->pokemon->getDisplayName(), $this->maxBoost()),
        );
    }

    private function ensureStonesAreOwned(): void
    {
        throw_if($this->ownedStones < 1, fn () => new InsufficientInventoryException($this->boostStone));
    }

    private function canUseAnotherStone(int $stonesUsed, int $boost): bool
    {
        return $stonesUsed < min($this->maxStones, $this->ownedStones)
            && $boost < $this->maxBoost()
            && ($this->targetBoost === null || $boost < $this->targetBoost);
    }

    private function maxBoost(): int
    {
        return (int) config('game.pokemon.max_boost');
    }
}
