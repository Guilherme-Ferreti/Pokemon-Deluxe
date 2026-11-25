<?php

declare(strict_types=1);

namespace App\Pokemon\Mechanics;

use InvalidArgumentException;

final class BoostStoneCalculator
{
    /**
     * @throws InvalidArgumentException
     */
    public function failureChance(int $currentBoost): float
    {
        $maxBoost = (int) config('game.pokemon.max_boost');

        if ($currentBoost < 0 || $currentBoost >= $maxBoost) {
            throw new InvalidArgumentException('A boost stone needs a boost between 0 and ' . ($maxBoost - 1) . ", [{$currentBoost}] given.");
        }

        $safeUntil = (int) config('game.boost.safe_until');

        if ($currentBoost < $safeUntil) {
            return 0.0;
        }

        return ($currentBoost - $safeUntil + 1) * (float) config('game.boost.failure_step');
    }
}
