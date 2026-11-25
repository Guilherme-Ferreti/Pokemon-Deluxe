<?php

declare(strict_types=1);

use App\Economy\Actions\SpendMoneyAction;
use App\Economy\Exceptions\NotEnoughMoneyException;
use Illuminate\Support\Facades\DB;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('spends the user\'s money and leaves other users alone', function () {
    $user      = User::factory()->withMoney(2000)->create();
    $otherUser = User::factory()->withMoney(300)->create();

    new SpendMoneyAction()->handle($user, 1200);

    assertDatabaseCount(User::class, 2);

    expect($user->fresh()->money)->toBe(800)
        ->and($otherUser->fresh()->money)->toBe(300);
});

it('spends all of the user\'s money', function () {
    $user = User::factory()->withMoney(600)->create();

    new SpendMoneyAction()->handle($user, 600);

    expect($user->fresh()->money)->toBe(0);
});

it('throws when spending more than the user has and leaves the money unchanged', function () {
    $user = User::factory()->withMoney(500)->create();

    expect(fn () => new SpendMoneyAction()->handle($user, 501))
        ->toThrow(NotEnoughMoneyException::class, 'You need ₽ 501 but only have ₽ 500.')
        ->and($user->fresh()->money)->toBe(500);
});

it('checks the saved money when the given user is stale', function () {
    $user      = User::factory()->withMoney(500)->create();
    $staleUser = User::query()->findOrFail($user->id);

    new SpendMoneyAction()->handle($user, 400);

    expect(fn () => new SpendMoneyAction()->handle($staleUser, 400))
        ->toThrow(NotEnoughMoneyException::class)
        ->and($user->fresh()->money)->toBe(100);
});

it('throws for an amount of zero or below', function (int $amount) {
    $user = User::factory()->withMoney(500)->create();

    expect(fn () => new SpendMoneyAction()->handle($user, $amount))
        ->toThrow(InvalidArgumentException::class)
        ->and($user->fresh()->money)->toBe(500);
})->with([
    'zero'     => [0],
    'negative' => [-1],
]);

it('leaves the money unchanged when the transaction is rolled back', function () {
    $user = User::factory()->withMoney(500)->create();

    expect(fn () => DB::transaction(function () use ($user): void {
        new SpendMoneyAction()->handle($user, 200);

        throw new RuntimeException('Rolling back the transaction.');
    }))->toThrow(RuntimeException::class)
        ->and($user->fresh()->money)->toBe(500);
});
