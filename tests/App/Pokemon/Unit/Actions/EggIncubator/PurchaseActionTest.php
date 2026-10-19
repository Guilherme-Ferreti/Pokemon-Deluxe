<?php

declare(strict_types=1);

use App\Pokemon\Actions\EggIncubator\PurchaseAction;
use App\Pokemon\Exceptions\EggIncubatorAlreadyPurchasedException;
use Shared\Models\User;
use Shared\ValueObjects\EggIncubatorIndex;

use function Pest\Laravel\assertDatabaseCount;

it('purchases an incubator the user has not purchased', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => []]);

    new PurchaseAction()->handle($user, new EggIncubatorIndex(1));

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_egg_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});

it('keeps the previously purchased incubators when purchasing another one', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    new PurchaseAction()->handle($user, new EggIncubatorIndex(2));

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_egg_incubator_indexes)
        ->toHaveCount(2)
        ->sequence(
            fn ($value) => $value->toMatchObject(['value' => 1]),
            fn ($value) => $value->toMatchObject(['value' => 2]),
        );
});

it('does not overwrite a purchase made after the user instance was loaded', function () {
    $user      = User::factory()->create(['purchased_egg_incubator_indexes' => []]);
    $staleUser = User::query()->findOrFail($user->id);

    new PurchaseAction()->handle($user, new EggIncubatorIndex(1));

    new PurchaseAction()->handle($staleUser, new EggIncubatorIndex(2));

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_egg_incubator_indexes)
        ->toHaveCount(2)
        ->sequence(
            fn ($value) => $value->toMatchObject(['value' => 1]),
            fn ($value) => $value->toMatchObject(['value' => 2]),
        );
});

it('does not purchase an incubator that has already been purchased', function () {
    $user = User::factory()->create(['purchased_egg_incubator_indexes' => [new EggIncubatorIndex(1)]]);

    expect(fn () => new PurchaseAction()->handle($user, new EggIncubatorIndex(1)))
        ->toThrow(EggIncubatorAlreadyPurchasedException::class);

    assertDatabaseCount(User::class, 1);

    expect($user->fresh()->purchased_egg_incubator_indexes)
        ->toHaveCount(1)
        ->sequence(fn ($value) => $value->toMatchObject(['value' => 1]));
});
