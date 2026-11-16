<?php

declare(strict_types=1);

use App\Battle\Actions\ForfeitBattleAction;
use App\Battle\Events\BattleEnded;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use Illuminate\Support\Facades\Event;
use Shared\Enums\BattleStatus;
use Shared\Models\Battle;

it('ends an ongoing battle as forfeited, even while awaiting a send-out', function (bool $isAwaitingSendOut) {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => $battle->state->withAwaitingSendOut($isAwaitingSendOut)]);

    $battle = new ForfeitBattleAction()->handle($battle)->fresh();

    expect($battle?->status)->toBe(BattleStatus::FORFEITED)
        ->and($battle?->state->log)->toBe(['You ran away!']);

    Event::assertDispatchedTimes(BattleEnded::class, 1);
    Event::assertDispatched(BattleEnded::class, fn (BattleEnded $event) => $event->status === BattleStatus::FORFEITED && ! $event->isWin);
})->with([
    'during a turn'       => [false],
    'awaiting a send-out' => [true],
]);

it('refuses to forfeit a battle that has already ended', function () {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['status' => BattleStatus::LOST]);

    expect(fn () => new ForfeitBattleAction()->handle($battle))
        ->toThrow(BattleAlreadyEndedException::class)
        ->and($battle->fresh()?->status)->toBe(BattleStatus::LOST);

    Event::assertNotDispatched(BattleEnded::class);
});
