<?php

declare(strict_types=1);

use Database\Seeders\AchievementSeeder;
use Database\Seeders\PokeballSeeder;
use Database\Seeders\SpecialItemSeeder;
use Shared\Enums\InventoryItemType;
use Shared\Enums\MissionCategory;
use Shared\Enums\MissionGoal;
use Shared\Enums\RewardItemPool;
use Shared\Models\Achievement;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\seed;

it('seeds the achievements from the file in order', function () {
    seed(AchievementSeeder::class);

    assertDatabaseCount(Achievement::class, 16);

    $beatAllGyms = Achievement::query()->where('slug', 'beat-all-gyms')->sole();

    expect(Achievement::query()->orderBy('sort_order')->value('slug'))->toBe('catch-10-pokemon')
        ->and($beatAllGyms->category)->toBe(MissionCategory::BATTLING)
        ->and($beatAllGyms->goal)->toBe(MissionGoal::BEAT_GYM)
        ->and($beatAllGyms->target)->toBe(72)
        ->and($beatAllGyms->reward->equals(new MissionReward(items: [
            new MissionRewardItem(quantity: 1, itemType: InventoryItemType::POKEBALL, slug: 'master-ball'),
        ])))->toBeTrue()
        ->and(Achievement::query()->where('slug', 'become-champion')->sole()->reward->items[0]->randomPool)->toBe(RewardItemPool::Z_MOVE_TM);
});

it('rewards only items that exist', function () {
    seed([PokeballSeeder::class, SpecialItemSeeder::class, AchievementSeeder::class]);

    $concreteItems = Achievement::all()
        ->flatMap(fn (Achievement $achievement) => $achievement->reward->items)
        ->reject(fn (MissionRewardItem $item) => $item->isRandom());

    expect($concreteItems)->not->toBeEmpty();

    foreach ($concreteItems as $item) {
        $model = match ($item->itemType) {
            InventoryItemType::POKEBALL       => Pokeball::class,
            InventoryItemType::SPECIAL_ITEM   => SpecialItem::class,
            InventoryItemType::EVOLUTION_ITEM => EvolutionItem::class,
            InventoryItemType::TM             => Move::class,
            null                              => throw new LogicException('A concrete reward item has an item type.'),
        };

        expect($model::query()->where('slug', $item->slug)->exists())->toBeTrue("[{$item->slug}] does not exist.");
    }
});
