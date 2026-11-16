<?php

declare(strict_types=1);

use App\Battle\Actions\EndBattleAction;
use App\Battle\Events\BattleEnded;
use Illuminate\Support\Facades\Event;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\Battle;

it('ends the battle and dispatches BattleEnded once', function (BattleStatus $status, bool $isWin) {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['type' => BattleType::WILD, 'status' => BattleStatus::ONGOING]);

    new EndBattleAction()->handle($battle, $status);

    expect($battle->fresh()?->status)->toBe($status);

    Event::assertDispatchedTimes(BattleEnded::class, 1);
    Event::assertDispatched(BattleEnded::class, fn (BattleEnded $event) => $event->battleId === $battle->id
        && $event->userId === $battle->user_id
        && $event->type === BattleType::WILD
        && $event->status === $status
        && $event->isWin === $isWin);
})->with([
    'won'       => [BattleStatus::WON, true],
    'caught'    => [BattleStatus::CAUGHT, true],
    'lost'      => [BattleStatus::LOST, false],
    'forfeited' => [BattleStatus::FORFEITED, false],
]);

it('refuses to end a battle that has already ended', function () {
    Event::fake([BattleEnded::class]);

    $battle = Battle::factory()->create(['status' => BattleStatus::WON]);

    expect(fn () => new EndBattleAction()->handle($battle, BattleStatus::LOST))
        ->toThrow(LogicException::class)
        ->and($battle->fresh()?->status)->toBe(BattleStatus::WON);

    Event::assertNotDispatched(BattleEnded::class);
});

it('refuses to end a battle as ongoing', function () {
    $battle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    expect(fn () => new EndBattleAction()->handle($battle, BattleStatus::ONGOING))
        ->toThrow(InvalidArgumentException::class);
});
