<?php

declare(strict_types=1);

namespace App\Shop\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use LogicException;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

final class BuyItemRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'itemType' => ['required', Rule::enum(InventoryItemType::class)->only($this->buyableItemTypes())],
            'itemId'   => ['required', 'string', ...$this->itemExistsRule()],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . (int) config('game.inventory.max_stack')],
        ];
    }

    /**
     * @return list<InventoryItemType>
     */
    private function buyableItemTypes(): array
    {
        return [InventoryItemType::POKEBALL, InventoryItemType::EVOLUTION_ITEM, InventoryItemType::TM];
    }

    /**
     * @return list<Exists>
     */
    private function itemExistsRule(): array
    {
        $itemType = $this->enum('itemType', InventoryItemType::class);

        if ($itemType === null || ! in_array($itemType, $this->buyableItemTypes(), true)) {
            return [];
        }

        return [Rule::exists($this->itemModelClass($itemType), 'id')];
    }

    /**
     * @return class-string<Pokeball|EvolutionItem|Move>
     */
    private function itemModelClass(?InventoryItemType $itemType): string
    {
        return match ($itemType) {
            InventoryItemType::POKEBALL       => Pokeball::class,
            InventoryItemType::EVOLUTION_ITEM => EvolutionItem::class,
            InventoryItemType::TM             => Move::class,
            default                           => throw new LogicException('Only validated, buyable item types have a model.'),
        };
    }
}
