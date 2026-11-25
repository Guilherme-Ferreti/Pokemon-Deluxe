<?php

declare(strict_types=1);

namespace App\Inventory\Http\Web\PageProps;

use Illuminate\Database\Eloquent\Collection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Shared\Enums\InventoryItemType;
use Shared\Enums\SpecialItemCategory;
use Shared\Helpers\ItemHelper;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\SpecialItem;

final class InventoryIndexPageProps implements ProvidesInertiaProperties
{
    /**
     * @return array<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'pokeballs'      => fn () => $this->getItemsProp(InventoryItemType::POKEBALL),
            'evolutionItems' => fn () => $this->getItemsProp(InventoryItemType::EVOLUTION_ITEM),
            'tms'            => fn () => $this->getItemsProp(InventoryItemType::TM),
            'boostStones'    => fn () => $this->getSpecialItemsProp(SpecialItemCategory::BOOST_STONE),
            'keyItems'       => fn () => $this->getSpecialItemsProp(SpecialItemCategory::KEY_ITEM),
        ];
    }

    /**
     * @return array<int, array{id: string, name: string, sprite: string|null, description: string, quantity: int}>
     */
    private function getItemsProp(InventoryItemType $itemType): array
    {
        return $this->toItemsProp(
            $this->ownedInventoryItems()->filter(fn (InventoryItem $inventoryItem) => $inventoryItem->item_type === $itemType),
        );
    }

    /**
     * @return array<int, array{id: string, name: string, sprite: string|null, description: string, quantity: int}>
     */
    private function getSpecialItemsProp(SpecialItemCategory $category): array
    {
        return $this->toItemsProp(
            $this->ownedInventoryItems()->filter(fn (InventoryItem $inventoryItem) => $inventoryItem->item instanceof SpecialItem && $inventoryItem->item->category === $category),
        );
    }

    /**
     * @param  Collection<int, InventoryItem>  $inventoryItems
     * @return array<int, array{id: string, name: string, sprite: string|null, description: string, quantity: int}>
     */
    private function toItemsProp(Collection $inventoryItems): array
    {
        return $inventoryItems
            ->sortBy(fn (InventoryItem $inventoryItem) => $inventoryItem->item instanceof Move ? $inventoryItem->item->tm_code : $inventoryItem->item->name)
            ->map(fn (InventoryItem $inventoryItem) => [
                'id'          => $inventoryItem->id,
                'name'        => ItemHelper::name($inventoryItem->item),
                'sprite'      => $inventoryItem->item instanceof Move ? $inventoryItem->item->tmSprite() : $inventoryItem->item->sprite,
                'description' => $inventoryItem->item instanceof Move ? $inventoryItem->item->tmDescription() : $inventoryItem->item->description,
                'quantity'    => $inventoryItem->quantity,
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    private function ownedInventoryItems(): Collection
    {
        return once(fn () => currentUser()->inventoryItems()->owned()->with('item')->get());
    }
}
