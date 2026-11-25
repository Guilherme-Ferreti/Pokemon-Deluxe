<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Override;
use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\Models\User;

/**
 * @extends Factory<InventoryItem>
 */
final class InventoryItemFactory extends Factory
{
    #[Override]
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'item_type' => InventoryItemType::POKEBALL,
            'item_id'   => Pokeball::factory(),
            'quantity'  => fake()->numberBetween(1, 10),
        ];
    }

    public function pokeball(Pokeball $pokeball): static
    {
        return $this->state([
            'item_type' => InventoryItemType::POKEBALL,
            'item_id'   => $pokeball->id,
        ]);
    }

    public function tm(Move $move): static
    {
        return $this->state([
            'item_type' => InventoryItemType::TM,
            'item_id'   => $move->id,
        ]);
    }

    public function specialItem(SpecialItem $specialItem): static
    {
        return $this->state([
            'item_type' => InventoryItemType::SPECIAL_ITEM,
            'item_id'   => $specialItem->id,
        ]);
    }

    public function evolutionItem(EvolutionItem $evolutionItem): static
    {
        return $this->state([
            'item_type' => InventoryItemType::EVOLUTION_ITEM,
            'item_id'   => $evolutionItem->id,
        ]);
    }
}
