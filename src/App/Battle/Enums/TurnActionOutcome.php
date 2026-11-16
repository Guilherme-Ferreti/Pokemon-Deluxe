<?php

declare(strict_types=1);

namespace App\Battle\Enums;

enum TurnActionOutcome: string
{
    case SKIPPED       = 'skipped';
    case MISSED        = 'missed';
    case NO_EFFECT     = 'no-effect';
    case USED          = 'used';
    case CAUSED_FLINCH = 'caused-flinch';
    case KNOCKED_OUT   = 'knocked-out';
}
