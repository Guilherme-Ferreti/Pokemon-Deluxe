<?php

declare(strict_types=1);

namespace Shared\Enums;

use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;

enum InventoryItemType: string
{
    case POKEBALL       = 'pokeball';
    case EVOLUTION_ITEM = 'evolution-item';
    case TM             = 'tm';
    case SPECIAL_ITEM   = 'special-item';

    public function label(): string
    {
        return match ($this) {
            self::POKEBALL       => 'Poké Ball',
            self::EVOLUTION_ITEM => 'Evolution Item',
            self::TM             => 'TM',
            self::SPECIAL_ITEM   => 'Special Item',
        };
    }

    public static function fromModel(EvolutionItem|Pokeball|Move|SpecialItem $model): self
    {
        return match ($model::class) {
            Pokeball::class      => self::POKEBALL,
            EvolutionItem::class => self::EVOLUTION_ITEM,
            Move::class          => self::TM,
            SpecialItem::class   => self::SPECIAL_ITEM,
        };
    }
}
