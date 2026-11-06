<?php

declare(strict_types=1);

namespace App\Pokemon\Services;

use InvalidArgumentException;

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

    private function experiencePerLevel(): int
    {
        return (int) config('game.pokemon.experience_per_level');
    }
}
