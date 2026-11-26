<?php

declare(strict_types=1);

use App\Inventory\Exceptions\InsufficientInventoryException;
use App\Pokemon\Actions\UseBoostStonesAction;
use App\Pokemon\Exceptions\MaxBoostReachedException;
use App\Pokemon\ValueObjects\BoostResult;
use Shared\Helpers\RandomHelper;
use Shared\Models\InventoryItem;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;
use Shared\Models\User;

it('uses one stone', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(14)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 5]);

    RandomHelper::fake([true]);

    $result = new UseBoostStonesAction()->handle($pokemon, 1);

    expect($result)->toEqual(new BoostResult(stonesUsed: 1, successes: 1, failures: 0, boostBefore: 14, boostAfter: 15))
        ->and($pokemon->fresh()->boost)->toBe(15)
        ->and($stack->fresh()->quantity)->toBe(4);
});

it('stops at the target boost', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(10)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 50]);

    RandomHelper::fake([true, true, true]);

    $result = new UseBoostStonesAction()->handle($pokemon, 20, targetBoost: 13);

    expect($result)->toEqual(new BoostResult(stonesUsed: 3, successes: 3, failures: 0, boostBefore: 10, boostAfter: 13))
        ->and($stack->fresh()->quantity)->toBe(47);
});

it('stops at the most stones to use', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(10)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 50]);

    RandomHelper::fake([true, true, true, true]);

    $result = new UseBoostStonesAction()->handle($pokemon, 4, targetBoost: 30);

    expect($result->stonesUsed)->toBe(4)
        ->and($result->boostAfter)->toBe(14)
        ->and($stack->fresh()->quantity)->toBe(46);
});

it('stops at the max boost', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(98)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 10]);

    RandomHelper::fake([true, true]);

    $result = new UseBoostStonesAction()->handle($pokemon, 10);

    expect($result)->toEqual(new BoostResult(stonesUsed: 2, successes: 2, failures: 0, boostBefore: 98, boostAfter: 100))
        ->and($stack->fresh()->quantity)->toBe(8);
});

it('stops when the player runs out of stones', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(5)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 2]);

    RandomHelper::fake([true, true]);

    $result = new UseBoostStonesAction()->handle($pokemon, 10);

    expect($result->stonesUsed)->toBe(2)
        ->and($result->boostAfter)->toBe(7)
        ->and($stack->fresh()->quantity)->toBe(0);
});

it('uses up failed stones without lowering the boost', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(60)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 10]);

    RandomHelper::fake([false, true, false]);

    $result = new UseBoostStonesAction()->handle($pokemon, 3);

    expect($result)->toEqual(new BoostResult(stonesUsed: 3, successes: 1, failures: 2, boostBefore: 60, boostAfter: 61))
        ->and($pokemon->fresh()->boost)->toBe(61)
        ->and($stack->fresh()->quantity)->toBe(7);
});

it('uses no stones when the target boost is already reached', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(30)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 10]);

    $result = new UseBoostStonesAction()->handle($pokemon, 5, targetBoost: 20);

    expect($result)->toEqual(new BoostResult(stonesUsed: 0, successes: 0, failures: 0, boostBefore: 30, boostAfter: 30))
        ->and($stack->fresh()->quantity)->toBe(10);
});

it('refuses a pokemon at the max boost', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(100)->create();
    $stack   = InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 10]);

    expect(fn () => new UseBoostStonesAction()->handle($pokemon, 1))->toThrow(MaxBoostReachedException::class)
        ->and($stack->fresh()->quantity)->toBe(10);
});

it('refuses without boost stones', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(10)->create();

    SpecialItem::factory()->boostStone()->create();

    expect(fn () => new UseBoostStonesAction()->handle($pokemon, 1))->toThrow(InsufficientInventoryException::class)
        ->and($pokemon->fresh()->boost)->toBe(10);
});
