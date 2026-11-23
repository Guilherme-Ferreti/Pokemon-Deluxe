<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\BattleLogKind;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-type BattleLogEntryArray array{turn: int, kind: string, text: string, move_name: string|null}
 */
final readonly class BattleLogEntry implements ValueObject
{
    public function __construct(
        public int $turn,
        public BattleLogKind $kind,
        public string $text,
        public ?string $moveName = null,
    ) {
        throw_if($turn < 0, InvalidArgumentException::class, "The turn cannot be negative, [{$turn}] given.");

        throw_if(
            ($kind === BattleLogKind::MOVE) !== ($moveName !== null),
            InvalidArgumentException::class,
            'Only a move entry has a move name, and every move entry has one.',
        );
    }

    /**
     * @param  BattleLogEntryArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            turn: $data['turn'],
            kind: BattleLogKind::from($data['kind']),
            text: $data['text'],
            moveName: $data['move_name'],
        );
    }

    /**
     * @return BattleLogEntryArray
     */
    public function toArray(): array
    {
        return [
            'turn'      => $this->turn,
            'kind'      => $this->kind->value,
            'text'      => $this->text,
            'move_name' => $this->moveName,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
