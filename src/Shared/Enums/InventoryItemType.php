<?php

declare(strict_types=1);

namespace Shared\Enums;

use Shared\Models\EvolutionItem;
use Shared\Models\Pokeball;

enum InventoryItemType: string
{
    case POKEBALL       = 'pokeball';
    case EVOLUTION_ITEM = 'evolution-item';

    public function label(): string
    {
        return match ($this) {
            self::POKEBALL       => 'Poké Ball',
            self::EVOLUTION_ITEM => 'Evolution Item',
        };
    }

    public static function fromModel(EvolutionItem|Pokeball $model): self
    {
        return match ($model::class) {
            Pokeball::class      => self::POKEBALL,
            EvolutionItem::class => self::EVOLUTION_ITEM,
        };
    }
}
