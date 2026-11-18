<?php

declare(strict_types=1);

use App\Battle\Actions\ForfeitBattleAction;
use App\Battle\Events\BattleEnded;
use App\Battle\Exceptions\BattleAlreadyEndedException;
use Illuminate\Support\Facades\Event;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\ValueObjects\BattleLogEntry;

it('ends an ongoing battle as forfeited and logs it, even while awaiting a send-out', function (BattleType $type, bool $isAwaitingSendOut, string $expectedText) {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['type' => $type, 'status' => BattleStatus::ONGOING]);
    $battle->update(['state' => $battle->state->withAwaitingSendOut($isAwaitingSendOut)]);

    $battle = new ForfeitBattleAction()->handle($battle)->fresh();

    expect($battle?->status)->toBe(BattleStatus::FORFEITED)
        ->and($battle?->state->log)->toEqual([new BattleLogEntry(1, BattleLogKind::RESULT, $expectedText)]);

    Event::assertDispatchedTimes(BattleEnded::class, 1);
    Event::assertDispatched(BattleEnded::class, fn (BattleEnded $event) => $event->status === BattleStatus::FORFEITED && ! $event->isWin);
})->with([
    'running from a wild battle'            => [BattleType::WILD, false, 'You got away safely!'],
    'running while awaiting a send-out'     => [BattleType::WILD, true, 'You got away safely!'],
    'forfeiting a battle against a trainer' => [BattleType::TRAINER, false, 'You forfeited the battle.'],
]);

it('refuses to forfeit a battle that has already ended', function () {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['status' => BattleStatus::LOST]);

    expect(fn () => new ForfeitBattleAction()->handle($battle))
        ->toThrow(BattleAlreadyEndedException::class)
        ->and($battle->fresh()?->status)->toBe(BattleStatus::LOST);

    Event::assertNotDispatched(BattleEnded::class);
});
