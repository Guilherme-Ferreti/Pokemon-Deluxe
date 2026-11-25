<?php

declare(strict_types=1);

use App\Economy\Exceptions\NotEnoughMoneyException;
use App\Pokemon\Actions\Incubator\PurchaseAction;
use App\Pokemon\Exceptions\IncubatorAlreadyPurchasedException;
use Shared\Models\User;
use Shared\ValueObjects\IncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;

it('purchases an incubator the user has not purchased', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => []]);

    new PurchaseAction()->handle($user, new IncubatorIndex(1));

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it('keeps the previously purchased incubators when purchasing another one', function () {
    $user = User::factory()->withMoney(5000)->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    new PurchaseAction()->handle($user, new IncubatorIndex(2));

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(2)
        ->sequence(
            fn ($value) => $value->toMatchObject(['value' => 1]),
            fn ($value) => $value->toMatchObject(['value' => 2]),
        );
});

it('does not overwrite a purchase made after the user instance was loaded', function () {
    $user      = User::factory()->withMoney(5000)->create(['purchased_incubator_indexes' => []]);
    $staleUser = User::query()->findOrFail($user->id);

    new PurchaseAction()->handle($user, new IncubatorIndex(1));

    new PurchaseAction()->handle($staleUser, new IncubatorIndex(2));

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(2)
        ->sequence(
            fn ($value) => $value->toMatchObject(['value' => 1]),
            fn ($value) => $value->toMatchObject(['value' => 2]),
        );
});

it('does not purchase an incubator that has already been purchased', function () {
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    expect(fn () => new PurchaseAction()->handle($user, new IncubatorIndex(1)))
        ->toThrow(IncubatorAlreadyPurchasedException::class);

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it('charges the next incubator\'s price', function (array $purchasedIndexes, int $incubatorIndex, int $moneyLeft) {
    $user = User::factory()->withMoney(12000)->create([
        'purchased_incubator_indexes' => array_map(fn (int $index) => new IncubatorIndex($index), $purchasedIndexes),
    ]);

    new PurchaseAction()->handle($user, new IncubatorIndex($incubatorIndex));

    expect($user->fresh()->money)->toBe($moneyLeft)
        ->and($user->fresh()->purchased_incubator_indexes)->toHaveCount(count($purchasedIndexes) + 1);
})->with([
    'the first is free'      => [[], 1, 12000],
    'the second costs 5,000' => [[1], 2, 7000],
    'the third costs 10,000' => [[1, 2], 3, 2000],
]);

it('refuses an incubator the player cannot afford and changes nothing', function () {
    $user = User::factory()->withMoney(4999)->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

    expect(fn () => new PurchaseAction()->handle($user, new IncubatorIndex(2)))
        ->toThrow(NotEnoughMoneyException::class, 'You need ₽ 5,000 but only have ₽ 4,999.')
        ->and($user->fresh()->money)->toBe(4999)
        ->and($user->fresh()->purchased_incubator_indexes)->toHaveCount(1);
});
