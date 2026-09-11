<?php

declare(strict_types=1);

namespace App\Dex\Enums;

enum SearchAttackdexSortBy: string
{
    case NAME           = 'name';
    case POWER          = 'power';
    case ACCURACY       = 'accuracy';
    case AILMENT_CHANCE = 'ailment-chance';
    case FLINCH_CHANCE  = 'flinch-chance';
}
