<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\InventoryItemType;
use Shared\Enums\RewardItemPool;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-type MissionRewardItemArray array{item_type?: string|null, slug?: string|null, random?: string|null, quantity: int}
 */
final readonly class MissionRewardItem implements ValueObject
{
    public function __construct(
        public int $quantity,
        public ?InventoryItemType $itemType = null,
        public ?string $slug = null,
        public ?RewardItemPool $randomPool = null,
    ) {
        throw_if($quantity < 1, InvalidArgumentException::class, "The quantity must be at least 1, [{$quantity}] given.");

        throw_if(
            ($itemType === null) !== ($slug === null),
            InvalidArgumentException::class,
            'A reward item needs both an item type and a slug, or neither.',
        );

        throw_if(
            ($itemType === null) === ($randomPool === null),
            InvalidArgumentException::class,
            'A reward item is either a concrete item or a random pool, not both or neither.',
        );
    }

    public function isRandom(): bool
    {
        return $this->randomPool !== null;
    }

    /**
     * @param  MissionRewardItemArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            quantity: $data['quantity'],
            itemType: isset($data['item_type']) ? InventoryItemType::from($data['item_type']) : null,
            slug: $data['slug'] ?? null,
            randomPool: isset($data['random']) ? RewardItemPool::from($data['random']) : null,
        );
    }

    /**
     * @return MissionRewardItemArray
     */
    public function toArray(): array
    {
        if ($this->randomPool !== null) {
            return [
                'random'   => $this->randomPool->value,
                'quantity' => $this->quantity,
            ];
        }

        return [
            'item_type' => $this->itemType?->value,
            'slug'      => $this->slug,
            'quantity'  => $this->quantity,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
