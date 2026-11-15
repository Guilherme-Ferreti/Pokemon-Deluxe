<?php

declare(strict_types=1);

namespace Shared\Enums;

enum ActionBlocker: string
{
    case FROZEN                   = 'frozen';
    case ASLEEP                   = 'asleep';
    case HURT_ITSELF_IN_CONFUSION = 'hurt-itself-in-confusion';
    case PARALYZED                = 'paralyzed';
}
