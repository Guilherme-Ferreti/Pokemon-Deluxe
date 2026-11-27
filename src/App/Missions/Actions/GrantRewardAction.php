<?php

declare(strict_types=1);

namespace App\Missions\Actions;

use App\Economy\Actions\AddMoneyAction;
use App\Inventory\Actions\AddInventoryItemAction;
use App\Missions\Exceptions\RewardOverflowException;
use App\Missions\Mechanics\RewardRoller;
use Illuminate\Support\Facades\DB;
use LogicException;
use Shared\Enums\InventoryItemType;
use Shared\Enums\RewardItemPool;
use Shared\Helpers\ItemHelper;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\SpeciesEvolution;
use Shared\Models\User;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

final class GrantRewardAction
{
    private User $user;

    private bool $acceptOverflow;

    private MissionReward $rolledReward;

    /**
     * @var list<array{item: EvolutionItem|Pokeball|Move|SpecialItem, fittingQuantity: int, overflowingQuantity: int}>
     */
    private array $itemGrants = [];

    public function handle(User $user, MissionReward $reward, bool $acceptOverflow = false): MissionReward
    {
        $this->user           = $user;
        $this->acceptOverflow = $acceptOverflow;
        $this->rolledReward   = new RewardRoller()->roll($reward, $this->loadSlugsByPool($reward));

        DB::transaction(function (): void {
            User::query()->whereKey($this->user->id)->lockForUpdate()->firstOrFail();

            $this->loadItemGrants();

            $this->ensureRewardFitsOrOverflowIsAccepted();

            $this->giveMoney();
            $this->giveItems();
        });

        return $this->rolledReward;
    }

    /**
     * @return array<string, list<string>>
     */
    private function loadSlugsByPool(MissionReward $reward): array
    {
        $pools = collect($reward->items)
            ->map(fn (MissionRewardItem $item): ?RewardItemPool => $item->randomPool)
            ->filter()
            ->unique(fn (RewardItemPool $pool): string => $pool->value);

        return $pools
            ->mapWithKeys(fn (RewardItemPool $pool): array => [$pool->value => $this->loadPoolSlugs($pool)])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function loadPoolSlugs(RewardItemPool $pool): array
    {
        $query = match ($pool) {
            RewardItemPool::NORMAL_TM  => Move::query()->where('is_z_move', false)->where('is_dynamax_move', false),
            RewardItemPool::Z_MOVE_TM  => Move::query()->where('is_z_move', true),
            RewardItemPool::DYNAMAX_TM => Move::query()->where('is_dynamax_move', true),
            RewardItemPool::MEGA_STONE => EvolutionItem::query()->whereIn(
                'id',
                SpeciesEvolution::query()
                    ->whereHas('into', fn ($query) => $query->where('is_mega', true))
                    ->select('evolution_item_id'),
            ),
        };

        /** @var list<string> */
        return $query->orderBy('slug')->pluck('slug')->all();
    }

    private function loadItemGrants(): void
    {
        $heldQuantities = [];

        foreach ($this->rolledReward->items as $rewardItem) {
            $item = $this->findItem($rewardItem);
            $key  = InventoryItemType::fromModel($item)->value . ':' . $item->getKey();

            $heldQuantities[$key] ??= $this->heldQuantity($item);

            $fittingQuantity = max(0, min($rewardItem->quantity, $this->maxStack() - $heldQuantities[$key]));

            $heldQuantities[$key] += $fittingQuantity;

            $this->itemGrants[] = [
                'item'                => $item,
                'fittingQuantity'     => $fittingQuantity,
                'overflowingQuantity' => $rewardItem->quantity - $fittingQuantity,
            ];
        }
    }

    private function findItem(MissionRewardItem $rewardItem): EvolutionItem|Pokeball|Move|SpecialItem
    {
        $query = match ($rewardItem->itemType) {
            InventoryItemType::POKEBALL       => Pokeball::query(),
            InventoryItemType::EVOLUTION_ITEM => EvolutionItem::query(),
            InventoryItemType::TM             => Move::query(),
            InventoryItemType::SPECIAL_ITEM   => SpecialItem::query(),
            null                              => throw new LogicException('A rolled reward item has no random pool left.'),
        };

        return $query->where('slug', $rewardItem->slug)->firstOrFail();
    }

    private function heldQuantity(EvolutionItem|Pokeball|Move|SpecialItem $item): int
    {
        return (int) InventoryItem::query()->whereBelongsTo($this->user)->forItem($item)->value('quantity');
    }

    private function ensureRewardFitsOrOverflowIsAccepted(): void
    {
        $overflowingItems = [];

        foreach ($this->itemGrants as $grant) {
            if ($grant['overflowingQuantity'] > 0) {
                $overflowingItems[] = number_format($grant['overflowingQuantity']) . ' ' . ItemHelper::name($grant['item'], $grant['overflowingQuantity']);
            }
        }

        throw_if($overflowingItems !== [] && ! $this->acceptOverflow, fn () => new RewardOverflowException($overflowingItems));
    }

    private function giveMoney(): void
    {
        if ($this->rolledReward->money > 0) {
            new AddMoneyAction()->handle($this->user, $this->rolledReward->money);
        }
    }

    private function giveItems(): void
    {
        foreach ($this->itemGrants as $grant) {
            if ($grant['fittingQuantity'] > 0) {
                new AddInventoryItemAction()->handle($this->user, $grant['item'], $grant['fittingQuantity']);
            }
        }
    }

    private function maxStack(): int
    {
        return (int) config('game.inventory.max_stack');
    }
}
