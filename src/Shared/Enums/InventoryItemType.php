<?php

declare(strict_types=1);

namespace Shared\Enums;

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
}
