<?php

declare(strict_types=1);

namespace Shared\Enums;

enum BattleType: string
{
    case WILD         = 'wild';
    case TRAINER      = 'trainer';
    case GYM          = 'gym';
    case ELITE_FOUR   = 'elite-four';
    case BATTLE_TOWER = 'battle-tower';
}
