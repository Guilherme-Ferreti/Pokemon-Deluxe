<?php

declare(strict_types=1);

use App\Economy\Actions\AddMoneyAction;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('adds money to the user and leaves other users alone', function () {
    $user      = User::factory()->withMoney(500)->create();
    $otherUser = User::factory()->withMoney(300)->create();

    new AddMoneyAction()->handle($user, 1200);

    assertDatabaseCount(User::class, 2);

    expect($user->fresh()->money)->toBe(1700)
        ->and($otherUser->fresh()->money)->toBe(300);
});

it('adds to the saved money when the given user is stale', function () {
    $user      = User::factory()->withMoney(500)->create();
    $staleUser = User::query()->findOrFail($user->id);

    new AddMoneyAction()->handle($user, 100);
    new AddMoneyAction()->handle($staleUser, 100);

    expect($user->fresh()->money)->toBe(700);
});

it('throws for an amount of zero or below', function (int $amount) {
    $user = User::factory()->withMoney(500)->create();

    expect(fn () => new AddMoneyAction()->handle($user, $amount))
        ->toThrow(InvalidArgumentException::class);

    expect($user->fresh()->money)->toBe(500);
})->with([
    'zero'     => [0],
    'negative' => [-1],
]);
