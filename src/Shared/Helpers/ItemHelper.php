<?php

declare(strict_types=1);

namespace Shared\Helpers;

use Illuminate\Support\Str;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;

final class ItemHelper
{
    public static function name(Pokeball|EvolutionItem|Move $item, int $quantity = 1): string
    {
        return $item instanceof Move ? $item->tmName() : Str::plural($item->name, $quantity);
    }
}
