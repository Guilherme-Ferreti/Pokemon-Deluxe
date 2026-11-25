<?php

declare(strict_types=1);

use Shared\Enums\InventoryItemType;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;

it('finds the item type of each inventory model', function (EvolutionItem|Pokeball|Move|SpecialItem $model, InventoryItemType $itemType) {
    expect(InventoryItemType::fromModel($model))->toBe($itemType);
})->with([
    'pokeball'       => fn () => [new Pokeball, InventoryItemType::POKEBALL],
    'evolution item' => fn () => [new EvolutionItem, InventoryItemType::EVOLUTION_ITEM],
    'tm'             => fn () => [new Move, InventoryItemType::TM],
    'special item'   => fn () => [new SpecialItem, InventoryItemType::SPECIAL_ITEM],
]);
