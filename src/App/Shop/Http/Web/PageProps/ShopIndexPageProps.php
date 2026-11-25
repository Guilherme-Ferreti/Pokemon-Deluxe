<?php

declare(strict_types=1);

namespace App\Shop\Http\Web\PageProps;

use App\Shop\Actions\GetTodaysTmRotationAction;
use App\Shop\Mechanics\ItemPriceCalculator;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\InventoryItemType;
use Shared\Helpers\ItemHelper;
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
            'pokeballs'           => $this->getPokeballsProp(...),
            'evolutionItems'      => $this->getEvolutionItemsProp(...),
            'tms'                 => $this->getTmsProp(...),
            'maxStack'            => (int) config('game.inventory.max_stack'),
            'confirmPurchaseOver' => (int) config('game.shop.confirm_purchase_over'),
        ];
    }

    /**
     * @return array<int, array{id: string, itemType: string, name: string, pluralName: string, sprite: string|null, effect: string, price: int|null, ownedQuantity: int}>
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
                'itemType'      => InventoryItemType::POKEBALL->value,
                'name'          => $pokeball->name,
                'pluralName'    => ItemHelper::name($pokeball, 2),
                'sprite'        => $pokeball->sprite,
                'effect'        => $pokeball->description,
                'price'         => $pokeball->price,
                'ownedQuantity' => $this->ownedQuantity($pokeball),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, itemType: string, name: string, pluralName: string, sprite: string|null, effect: string, price: int|null, ownedQuantity: int}>
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
                'itemType'      => InventoryItemType::EVOLUTION_ITEM->value,
                'name'          => $evolutionItem->name,
                'pluralName'    => ItemHelper::name($evolutionItem, 2),
                'sprite'        => $evolutionItem->sprite,
                'effect'        => $evolutionItem->description,
                'price'         => $evolutionItem->price,
                'ownedQuantity' => $this->ownedQuantity($evolutionItem),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, itemType: string, name: string, pluralName: string, sprite: string, type: string, power: int|null, price: int|null, ownedQuantity: int}>
     */
    private function getTmsProp(): array
    {
        $tmRotation          = new GetTodaysTmRotationAction()->handle();
        $itemPriceCalculator = new ItemPriceCalculator;

        return Move::query()
            ->whereKey($tmRotation->move_ids)
            ->orderBy('tm_code')
            ->get()
            ->map(fn (Move $move) => [
                'id'            => $move->id,
                'itemType'      => InventoryItemType::TM->value,
                'name'          => $move->tmName(),
                'pluralName'    => ItemHelper::name($move, 2),
                'sprite'        => $move->tmSprite(),
                'type'          => $move->type->value,
                'power'         => $move->power,
                'price'         => $itemPriceCalculator->price($move),
                'ownedQuantity' => $this->ownedQuantity($move),
            ])
            ->values()
            ->all();
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
