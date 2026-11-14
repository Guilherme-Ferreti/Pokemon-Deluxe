<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Enums\PokemonType;

final class DamageCalculator
{
    public function calculateDamageMultiplier(
        PokemonType $attackType,
        PokemonType $defenderType,
        ?PokemonType $secondDefenderType = null
    ): float {
        $multiplier = $attackType->effectiveness()[$defenderType->value];

        if ($secondDefenderType instanceof PokemonType) {
            $multiplier *= $attackType->effectiveness()[$secondDefenderType->value];
        }

        return $multiplier;
    }
}
