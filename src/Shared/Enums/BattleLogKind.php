<?php

declare(strict_types=1);

namespace Shared\Enums;

enum BattleLogKind: string
{
    case MOVE   = 'move';
    case DAMAGE = 'damage';
    case DETAIL = 'detail';
    case INFO   = 'info';
    case EVENT  = 'event';
    case RESULT = 'result';
}
