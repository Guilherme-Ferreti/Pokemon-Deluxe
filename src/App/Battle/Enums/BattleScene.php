<?php

declare(strict_types=1);

namespace App\Battle\Enums;

use Shared\Enums\BattleType;

enum BattleScene: string
{
    case GRASS_FIELD    = 'grass-field';
    case TRAINER_ARENA  = 'trainer-arena';
    case GYM            = 'gym';
    case INDIGO_PLATEAU = 'indigo-plateau';
    case BATTLE_TOWER   = 'battle-tower';

    public static function fromBattleType(BattleType $battleType): self
    {
        return match ($battleType) {
            BattleType::WILD         => self::GRASS_FIELD,
            BattleType::TRAINER      => self::TRAINER_ARENA,
            BattleType::GYM          => self::GYM,
            BattleType::ELITE_FOUR   => self::INDIGO_PLATEAU,
            BattleType::BATTLE_TOWER => self::BATTLE_TOWER,
        };
    }
}
