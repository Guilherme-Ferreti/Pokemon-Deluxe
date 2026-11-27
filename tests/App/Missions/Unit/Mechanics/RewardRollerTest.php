<?php

declare(strict_types=1);

use App\Missions\Mechanics\RewardRoller;
use Shared\Enums\InventoryItemType;
use Shared\Enums\RewardItemPool;
use Shared\Helpers\RandomHelper;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

it('replaces each random item with an item picked from its pool', function () {
    RandomHelper::fake(['gengarite', 'ice-beam']);

    $reward = new MissionReward(money: 3_000, items: [
        new MissionRewardItem(quantity: 1, randomPool: RewardItemPool::MEGA_STONE),
        new MissionRewardItem(quantity: 2, itemType: InventoryItemType::SPECIAL_ITEM, slug: 'boost-stone'),
        new MissionRewardItem(quantity: 1, randomPool: RewardItemPool::NORMAL_TM),
    ]);

    $rolledReward = new RewardRoller()->roll($reward, [
        'mega-stone' => ['gengarite', 'charizardite-x'],
        'normal-tm'  => ['ice-beam', 'thunderbolt'],
    ]);

    expect($rolledReward->equals(new MissionReward(money: 3_000, items: [
        new MissionRewardItem(quantity: 1, itemType: InventoryItemType::EVOLUTION_ITEM, slug: 'gengarite'),
        new MissionRewardItem(quantity: 2, itemType: InventoryItemType::SPECIAL_ITEM, slug: 'boost-stone'),
        new MissionRewardItem(quantity: 1, itemType: InventoryItemType::TM, slug: 'ice-beam'),
    ])))->toBeTrue();
});

it('refuses a pool with nothing to pick from', function () {
    $reward = new MissionReward(items: [new MissionRewardItem(quantity: 1, randomPool: RewardItemPool::DYNAMAX_TM)]);

    expect(fn () => new RewardRoller()->roll($reward, ['dynamax-tm' => []]))->toThrow(InvalidArgumentException::class);
});
