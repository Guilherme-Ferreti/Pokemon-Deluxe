<?php

declare(strict_types=1);

use App\Missions\Actions\GrantRewardAction;
use App\Missions\Exceptions\RewardOverflowException;
use Shared\Enums\InventoryItemType;
use Shared\Enums\RewardItemPool;
use Shared\Helpers\RandomHelper;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
use Shared\Models\User;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('gives the money and the items', function () {
    $user        = User::factory()->withMoney(500)->create();
    $pokeball    = Pokeball::factory()->create(['slug' => 'poke-ball']);
    $boostStone  = SpecialItem::factory()->boostStone()->create();
    $boostStones = InventoryItem::factory()->for($user)->specialItem($boostStone)->create(['quantity' => 4]);

    new GrantRewardAction()->handle($user, new MissionReward(money: 1_000, items: [
        new MissionRewardItem(quantity: 5, itemType: InventoryItemType::POKEBALL, slug: 'poke-ball'),
        new MissionRewardItem(quantity: 2, itemType: InventoryItemType::SPECIAL_ITEM, slug: $boostStone->slug),
    ]));

    assertDatabaseCount(InventoryItem::class, 2);
    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_id' => $pokeball->id, 'quantity' => 5]);

    expect($user->fresh()->money)->toBe(1_500)
        ->and($boostStones->fresh()->quantity)->toBe(6);
});

it('gives an item picked from a random pool and returns what was given', function () {
    RandomHelper::fake(['ice-beam']);

    $user    = User::factory()->create();
    $iceBeam = Move::factory()->create(['slug' => 'ice-beam', 'is_z_move' => false, 'is_dynamax_move' => false]);

    $rolledReward = new GrantRewardAction()->handle($user, new MissionReward(items: [
        new MissionRewardItem(quantity: 1, randomPool: RewardItemPool::NORMAL_TM),
    ]));

    assertDatabaseCount(InventoryItem::class, 1);
    assertDatabaseHas(InventoryItem::class, ['user_id' => $user->id, 'item_type' => InventoryItemType::TM, 'item_id' => $iceBeam->id, 'quantity' => 1]);

    expect($rolledReward->items[0]->slug)->toBe('ice-beam');
});

it('picks a random item only from its own pool', function (RewardItemPool $pool, Closure $createPoolItem) {
    $user = User::factory()->create();

    Move::factory()->create(['is_z_move' => false, 'is_dynamax_move' => false]);
    Move::factory()->create(['is_z_move' => true, 'is_dynamax_move' => false]);
    Move::factory()->create(['is_z_move' => false, 'is_dynamax_move' => true]);
    SpeciesEvolution::factory()->create([
        'into_species_id'   => Species::factory()->create(['is_mega' => true])->id,
        'evolution_item_id' => EvolutionItem::factory(),
    ]);
    SpeciesEvolution::factory()->create([
        'into_species_id'   => Species::factory()->create(['is_mega' => false])->id,
        'evolution_item_id' => EvolutionItem::factory(),
    ]);

    $poolItem = $createPoolItem();

    foreach (range(1, 5) as $attempt) {
        $rolledReward = new GrantRewardAction()->handle($user, new MissionReward(items: [
            new MissionRewardItem(quantity: 1, randomPool: $pool),
        ]));

        expect($rolledReward->items[0]->slug)->toBe($poolItem);
    }
})->with([
    'normal TM'  => [RewardItemPool::NORMAL_TM, fn () => Move::query()->where('is_z_move', false)->where('is_dynamax_move', false)->sole()->slug],
    'Z-Move TM'  => [RewardItemPool::Z_MOVE_TM, fn () => Move::query()->where('is_z_move', true)->sole()->slug],
    'Dynamax TM' => [RewardItemPool::DYNAMAX_TM, fn () => Move::query()->where('is_dynamax_move', true)->sole()->slug],
    'Mega Stone' => [RewardItemPool::MEGA_STONE, fn () => SpeciesEvolution::query()->whereRelation('into', 'is_mega', true)->sole()->evolutionItem->slug],
]);

it('refuses a reward that does not fit and lists what would not fit', function () {
    config(['game.inventory.max_stack' => 10]);

    $user        = User::factory()->withMoney(500)->create();
    $pokeball    = Pokeball::factory()->create(['name' => 'Poké Ball']);
    $boostStone  = SpecialItem::factory()->boostStone()->create();
    $pokeballs   = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 8]);
    $boostStones = InventoryItem::factory()->for($user)->specialItem($boostStone)->create(['quantity' => 9]);

    $grant = fn () => new GrantRewardAction()->handle($user, new MissionReward(money: 1_000, items: [
        new MissionRewardItem(quantity: 5, itemType: InventoryItemType::POKEBALL, slug: $pokeball->slug),
        new MissionRewardItem(quantity: 1, itemType: InventoryItemType::SPECIAL_ITEM, slug: $boostStone->slug),
    ]));

    expect($grant)->toThrow(function (RewardOverflowException $exception) {
        expect($exception->overflowingItems)->toBe(['3 Poké Balls']);
    });

    assertDatabaseCount(InventoryItem::class, 2);

    expect($user->fresh()->money)->toBe(500)
        ->and($pokeballs->fresh()->quantity)->toBe(8)
        ->and($boostStones->fresh()->quantity)->toBe(9);
});

it('counts the same item twice in one reward against one stack', function () {
    config(['game.inventory.max_stack' => 10]);

    $user       = User::factory()->create();
    $boostStone = SpecialItem::factory()->boostStone()->create();

    $grant = fn () => new GrantRewardAction()->handle($user, new MissionReward(items: [
        new MissionRewardItem(quantity: 6, itemType: InventoryItemType::SPECIAL_ITEM, slug: $boostStone->slug),
        new MissionRewardItem(quantity: 6, itemType: InventoryItemType::SPECIAL_ITEM, slug: $boostStone->slug),
    ]));

    expect($grant)->toThrow(RewardOverflowException::class);

    assertDatabaseCount(InventoryItem::class, 0);
});

it('fills the stack and drops the rest when the overflow is accepted', function () {
    config(['game.inventory.max_stack' => 10]);

    $user      = User::factory()->withMoney(500)->create();
    $pokeball  = Pokeball::factory()->create();
    $pokeballs = InventoryItem::factory()->for($user)->pokeball($pokeball)->create(['quantity' => 8]);

    new GrantRewardAction()->handle(
        user: $user,
        reward: new MissionReward(money: 1_000, items: [
            new MissionRewardItem(quantity: 5, itemType: InventoryItemType::POKEBALL, slug: $pokeball->slug),
        ]),
        acceptOverflow: true,
    );

    assertDatabaseCount(InventoryItem::class, 1);

    expect($user->fresh()->money)->toBe(1_500)
        ->and($pokeballs->fresh()->quantity)->toBe(10);
});
