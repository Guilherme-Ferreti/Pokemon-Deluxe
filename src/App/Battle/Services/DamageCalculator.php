<?php

declare(strict_types=1);

namespace App\Battle\Services;

use Shared\Enums\ElementalType;

final class DamageCalculator
{
    public function calculateDamageMultiplier(
        ElementalType $attackType,
        ElementalType $defenderType,
        ?ElementalType $secondDefenderType = null
    ): float {
        $multiplier = $attackType->effectiveness()[$defenderType->value];

        if ($secondDefenderType instanceof ElementalType) {
            $multiplier *= $attackType->effectiveness()[$secondDefenderType->value];
        }

        return $multiplier;
    }
}
