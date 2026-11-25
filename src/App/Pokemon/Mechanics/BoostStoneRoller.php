<?php

declare(strict_types=1);

namespace App\Pokemon\Mechanics;

use Shared\Helpers\RandomHelper;

final class BoostStoneRoller
{
    public function roll(int $currentBoost): bool
    {
        return RandomHelper::chance(100 - new BoostStoneCalculator()->failureChance($currentBoost));
    }
}
