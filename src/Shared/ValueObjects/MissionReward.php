<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-import-type MissionRewardItemArray from MissionRewardItem
 *
 * @phpstan-type MissionRewardArray array{money: int, items: list<MissionRewardItemArray>}
 */
final readonly class MissionReward implements ValueObject
{
    /**
     * @param  list<MissionRewardItem>  $items
     */
    public function __construct(
        public int $money = 0,
        public array $items = [],
    ) {
        throw_if($money < 0, InvalidArgumentException::class, "The money cannot be negative, [{$money}] given.");
    }

    /**
     * @param  MissionRewardArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            money: $data['money'],
            items: array_map(MissionRewardItem::fromArray(...), $data['items']),
        );
    }

    /**
     * @return MissionRewardArray
     */
    public function toArray(): array
    {
        return [
            'money' => $this->money,
            'items' => array_map(fn (MissionRewardItem $item): array => $item->toArray(), $this->items),
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
