<?php

declare(strict_types=1);

namespace Shared\Enums;

enum BattleStatus: string
{
    case ONGOING   = 'ongoing';
    case WON       = 'won';
    case LOST      = 'lost';
    case FORFEITED = 'forfeited';
    case CAUGHT    = 'caught';
}
