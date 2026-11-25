<?php

declare(strict_types=1);

namespace App\Shop\Http\Web\PageProps;

use App\Shop\Actions\GetTodaysTmRotationAction;
use App\Shop\Mechanics\ItemPriceCalculator;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

final class ShopIndexPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokeballs'      => $this->getPokeballsProp(...),
            'evolutionItems' => $this->getEvolutionItemsProp(...),
            'tmRotation'     => $this->getTmRotationProp(...),
        ];
    }

    /**
     * @return array<int, array{id: string, name: string, sprite: string|null, effect: string, price: int|null, ownedQuantity: int}>
     */
    private function getPokeballsProp(): array
    {
        return Pokeball::query()
            ->whereNotNull('price')
            ->orderBy('price')
            ->orderBy('name')
            ->get()
            ->map(fn (Pokeball $pokeball) => [
                'id'            => $pokeball->id,
                'name'          => $pokeball->name,
                'sprite'        => $pokeball->sprite,
                'effect'        => $pokeball->description,
                'price'         => $pokeball->price,
                'ownedQuantity' => $this->ownedQuantity($pokeball),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string, sprite: string|null, effect: string, price: int|null, ownedQuantity: int}>
     */
    private function getEvolutionItemsProp(): array
    {
        return EvolutionItem::query()
            ->whereNotNull('price')
            ->orderBy('price')
            ->orderBy('name')
            ->get()
            ->map(fn (EvolutionItem $evolutionItem) => [
                'id'            => $evolutionItem->id,
                'name'          => $evolutionItem->name,
                'sprite'        => $evolutionItem->sprite,
                'effect'        => $evolutionItem->description,
                'price'         => $evolutionItem->price,
                'ownedQuantity' => $this->ownedQuantity($evolutionItem),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{startsAt: string, endsAt: string, tms: array<int, array{id: string, name: string, sprite: string, type: string, power: int|null, price: int|null, ownedQuantity: int}>}
     */
    private function getTmRotationProp(): array
    {
        $tmRotation          = new GetTodaysTmRotationAction()->handle();
        $itemPriceCalculator = new ItemPriceCalculator;

        return [
            'startsAt' => $tmRotation->date->startOfDay()->toIso8601String(),
            'endsAt'   => $tmRotation->date->addDay()->startOfDay()->toIso8601String(),
            'tms'      => Move::query()
                ->whereKey($tmRotation->move_ids)
                ->orderBy('tm_code')
                ->get()
                ->map(fn (Move $move) => [
                    'id'            => $move->id,
                    'name'          => $move->tmName(),
                    'sprite'        => $move->tmSprite(),
                    'type'          => $move->type->value,
                    'power'         => $move->power,
                    'price'         => $itemPriceCalculator->price($move),
                    'ownedQuantity' => $this->ownedQuantity($move),
                ])
                ->values()
                ->all(),
        ];
    }

    private function ownedQuantity(EvolutionItem|Pokeball|Move $item): int
    {
        return $this->ownedQuantities()[$this->ownedQuantityKey(InventoryItemType::fromModel($item), $item->getKey())] ?? 0;
    }

    /**
     * @return array<string, int>
     */
    private function ownedQuantities(): array
    {
        return once(fn () => currentUser()
            ->inventoryItems()
            ->get()
            ->mapWithKeys(fn (InventoryItem $inventoryItem) => [
                $this->ownedQuantityKey($inventoryItem->item_type, $inventoryItem->item_id) => $inventoryItem->quantity,
            ])
            ->all());
    }

    private function ownedQuantityKey(InventoryItemType $itemType, string $itemId): string
    {
        return $itemType->value . ':' . $itemId;
    }
}
