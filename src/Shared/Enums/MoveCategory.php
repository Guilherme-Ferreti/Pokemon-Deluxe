<?php

declare(strict_types=1);

namespace Shared\Enums;

enum MoveCategory: string
{
    case DAMAGE         = 'damage';
    case AILMENT        = 'ailment';
    case DAMAGE_AILMENT = 'damage-ailment';

    public function label(): string
    {
        return match ($this) {
            self::DAMAGE         => 'Damage',
            self::AILMENT        => 'Ailment',
            self::DAMAGE_AILMENT => 'Damage & Ailment',
        };
    }

    /**
     * @return array<int, MoveDamageClass>
     */
    public function possibleDamageClasses(): array
    {
        return match ($this) {
            self::DAMAGE         => [MoveDamageClass::PHYSICAL, MoveDamageClass::SPECIAL],
            self::AILMENT        => [MoveDamageClass::STATUS],
            self::DAMAGE_AILMENT => [MoveDamageClass::PHYSICAL, MoveDamageClass::SPECIAL],
        };
    }
}
