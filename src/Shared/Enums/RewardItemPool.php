<?php

declare(strict_types=1);

namespace Shared\Enums;

enum RewardItemPool: string
{
    case NORMAL_TM  = 'normal-tm';
    case MEGA_STONE = 'mega-stone';
    case Z_MOVE_TM  = 'z-move-tm';
    case DYNAMAX_TM = 'dynamax-tm';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL_TM  => 'Random TM',
            self::MEGA_STONE => 'Random Mega Stone',
            self::Z_MOVE_TM  => 'Random Z-Move TM',
            self::DYNAMAX_TM => 'Random Dynamax TM',
        };
    }

    public function itemType(): InventoryItemType
    {
        return match ($this) {
            self::NORMAL_TM, self::Z_MOVE_TM, self::DYNAMAX_TM => InventoryItemType::TM,
            self::MEGA_STONE                                   => InventoryItemType::EVOLUTION_ITEM,
        };
    }
}
