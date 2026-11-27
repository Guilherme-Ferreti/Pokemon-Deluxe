<?php

declare(strict_types=1);

namespace App\Missions\Mechanics;

use InvalidArgumentException;
use Shared\Enums\RewardItemPool;
use Shared\Helpers\RandomHelper;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

final class RewardRoller
{
    /**
     * @param  array<string, list<string>>  $slugsByPool
     */
    public function roll(MissionReward $reward, array $slugsByPool): MissionReward
    {
        return new MissionReward(
            money: $reward->money,
            items: array_map(
                fn (MissionRewardItem $item): MissionRewardItem => $item->randomPool instanceof RewardItemPool
                    ? $this->rollPoolItem($item->randomPool, $item->quantity, $slugsByPool)
                    : $item,
                $reward->items,
            ),
        );
    }

    /**
     * @param  array<string, list<string>>  $slugsByPool
     */
    private function rollPoolItem(RewardItemPool $pool, int $quantity, array $slugsByPool): MissionRewardItem
    {
        $slugs = $slugsByPool[$pool->value] ?? [];

        throw_if($slugs === [], InvalidArgumentException::class, "The [{$pool->value}] pool has no items to pick from.");

        return new MissionRewardItem(
            quantity: $quantity,
            itemType: $pool->itemType(),
            slug: RandomHelper::pick($slugs),
        );
    }
}
