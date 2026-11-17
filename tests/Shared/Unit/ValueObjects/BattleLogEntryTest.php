<?php

declare(strict_types=1);

use Shared\Enums\BattleLogKind;
use Shared\ValueObjects\BattleLogEntry;

it('rejects invalid values', function (int $turn, BattleLogKind $kind, ?string $moveName) {
    expect(fn () => new BattleLogEntry($turn, $kind, 'Pikachu used Tackle!', $moveName))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'negative turn'                  => [-1, BattleLogKind::MOVE, 'Tackle'],
    'move entry without a move name' => [1, BattleLogKind::MOVE, null],
    'other entry with a move name'   => [1, BattleLogKind::EVENT, 'Tackle'],
]);
