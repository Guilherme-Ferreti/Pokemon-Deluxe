<?php

declare(strict_types=1);

namespace App\Pokemon\Mechanics;

use InvalidArgumentException;
use Shared\Enums\BattleType;

final class ExperienceCalculator
{
    public function experienceForLevel(int $level): int
    {
        throw_if($level < 1, InvalidArgumentException::class, 'The level must be at least 1.');

        return $level * $this->experiencePerLevel();
    }

    public function levelForExperience(int $experience): int
    {
        throw_if($experience < 0, InvalidArgumentException::class, 'The experience cannot be negative.');

        $level = intdiv($experience, $this->experiencePerLevel());

        return min($level, config('game.pokemon.max_level'));
    }

    public function experienceForDefeating(int $opponentLevel, int $pokemonLevel, BattleType $battleType, bool $isKnockout): int
    {
        throw_if($opponentLevel < 1 || $pokemonLevel < 1, InvalidArgumentException::class, 'Both levels must be at least 1.');

        $levelRatio = max(
            (float) config('game.experience.min_ratio'),
            min((float) config('game.experience.max_ratio'), $opponentLevel / $pokemonLevel),
        );

        $experience = (int) config('game.experience.base')
            * $levelRatio
            * (float) config("game.experience.battle_bonuses.{$battleType->value}")
            * ($isKnockout ? (float) config('game.experience.knockout_multiplier') : 1.0);

        return (int) floor($experience);
    }

    private function experiencePerLevel(): int
    {
        return (int) config('game.pokemon.experience_per_level');
    }
}
