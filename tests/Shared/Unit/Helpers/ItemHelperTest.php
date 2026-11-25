<?php

declare(strict_types=1);

use Shared\Helpers\ItemHelper;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

it('names an item for a quantity', function (Pokeball|EvolutionItem|Move $item, int $quantity, string $expected) {
    expect(ItemHelper::name($item, $quantity))->toBe($expected);
})->with([
    'one ball'             => fn () => [Pokeball::factory()->make(['name' => 'Ultra Ball']), 1, 'Ultra Ball'],
    'many balls'           => fn () => [Pokeball::factory()->make(['name' => 'Ultra Ball']), 10, 'Ultra Balls'],
    'many evolution items' => fn () => [EvolutionItem::factory()->make(['name' => 'Fire Stone']), 2, 'Fire Stones'],
    'many tms'             => fn () => [Move::factory()->make(['name' => 'Ice Beam', 'tm_code' => 'TM395']), 2, 'TM 395 Ice Beam'],
]);
