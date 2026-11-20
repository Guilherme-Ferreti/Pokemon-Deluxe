<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use InvalidArgumentException;
use Shared\Enums\Ailment;
use Shared\Models\Species;

final class CatchChanceCalculator
{
    /**
     * @throws InvalidArgumentException
     */
    public function calculate(Species $species, int $currentHp, int $maxHp, ?Ailment $ailment, float $ballMultiplier): float
    {
        throw_if($maxHp < 1 || $currentHp < 0 || $currentHp > $maxHp, InvalidArgumentException::class, "The HP must be between 0 and a max HP of at least 1, [{$currentHp}/{$maxHp}] given.");

        $chance = $this->baseChance($species)
            * $ballMultiplier
            * $this->lowHpMultiplier($currentHp / $maxHp)
            * $this->ailmentMultiplier($ailment);

        return min(100.0, $chance);
    }

    private function baseChance(Species $species): float
    {
        return $species->is_legendary || $species->is_mythical
            ? (float) config('game.catching.legendary_base_chance')
            : (float) config('game.catching.base_chance');
    }

    private function lowHpMultiplier(float $hpRatio): float
    {
        return 1 + (float) config('game.catching.low_hp_bonus') * (1 - $hpRatio);
    }

    private function ailmentMultiplier(?Ailment $ailment): float
    {
        if (! $ailment instanceof Ailment) {
            return 1.0;
        }

        return (float) config("game.catching.ailment_multipliers.{$ailment->value}", 1);
    }
}
