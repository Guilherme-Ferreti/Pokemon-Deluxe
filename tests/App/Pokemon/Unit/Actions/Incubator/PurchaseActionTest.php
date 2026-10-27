<?php

declare(strict_types=1);

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
    $user = User::factory()->create(['purchased_incubator_indexes' => [new IncubatorIndex(1)]]);

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
    $user      = User::factory()->create(['purchased_incubator_indexes' => []]);
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
