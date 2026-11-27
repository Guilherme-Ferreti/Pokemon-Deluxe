<?php

declare(strict_types=1);

use Shared\Enums\InventoryItemType;
use Shared\Enums\RewardItemPool;
use Shared\Models\Achievement;
use Shared\Models\UserMission;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

it('rejects negative money', function () {
    expect(fn () => new MissionReward(money: -1))->toThrow(InvalidArgumentException::class);
});

it('rejects invalid reward items', function (int $quantity, ?InventoryItemType $itemType, ?string $slug, ?RewardItemPool $randomPool) {
    expect(fn () => new MissionRewardItem($quantity, $itemType, $slug, $randomPool))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'zero quantity'           => [0, InventoryItemType::POKEBALL, 'poke-ball', null],
    'item type without slug'  => [1, InventoryItemType::POKEBALL, null, null],
    'slug without item type'  => [1, null, 'poke-ball', null],
    'neither item nor pool'   => [1, null, null, null],
    'both an item and a pool' => [1, InventoryItemType::TM, 'ice-beam', RewardItemPool::NORMAL_TM],
]);

it('round-trips through the database', function (string $model) {
    $reward = new MissionReward(money: 1_500, items: [
        new MissionRewardItem(quantity: 3, itemType: InventoryItemType::POKEBALL, slug: 'great-ball'),
        new MissionRewardItem(quantity: 1, randomPool: RewardItemPool::NORMAL_TM),
    ]);

    $record = $model::factory()->create(['reward' => $reward]);

    expect($record->fresh()->reward->equals($reward))->toBeTrue();
})->with([
    'achievement'  => [Achievement::class],
    'user mission' => [UserMission::class],
]);
