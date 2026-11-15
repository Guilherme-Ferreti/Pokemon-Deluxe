<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use InvalidArgumentException;

final class TurnOrderCalculator
{
    /**
     * @throws InvalidArgumentException
     */
    public function firstMoverChance(int $level, int $opponentLevel): float
    {
        throw_if($level < 1 || $opponentLevel < 1, InvalidArgumentException::class, "Levels must be at least 1, [{$level}] and [{$opponentLevel}] given.");

        return 100 * $level / ($level + $opponentLevel);
    }
}
