<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Helpers\RandomHelper;

final class TurnOrderRoller
{
    public function rollIsPlayerFirst(int $playerLevel, int $opponentLevel): bool
    {
        return RandomHelper::chance(new TurnOrderCalculator()->firstMoverChance($playerLevel, $opponentLevel));
    }
}
