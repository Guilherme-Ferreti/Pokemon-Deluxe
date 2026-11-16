<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use App\Pokemon\Mechanics\ExperienceCalculator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shared\Models\Pokemon;

final class AwardExperienceAction
{
    private Pokemon $pokemon;

    private int $experience;

    public function handle(Pokemon $pokemon, int $experience): Pokemon
    {
        $this->pokemon    = $pokemon;
        $this->experience = $experience;

        return DB::transaction(function () {
            $this->lockPokemon();

            $this->ensureExperienceIsNotNegative();

            $this->pokemon->update([
                'experience' => $this->newExperience(),
                'level'      => new ExperienceCalculator()->levelForExperience($this->newExperience()),
            ]);

            return $this->pokemon;
        });
    }

    private function lockPokemon(): void
    {
        Pokemon::query()->whereKey($this->pokemon->id)->lockForUpdate()->firstOrFail();

        $this->pokemon->refresh();
    }

    private function ensureExperienceIsNotNegative(): void
    {
        throw_if($this->experience < 0, InvalidArgumentException::class, "The experience cannot be negative, [{$this->experience}] given.");
    }

    private function newExperience(): int
    {
        $maxExperience = new ExperienceCalculator()->experienceForLevel((int) config('game.pokemon.max_level'));

        return max($this->pokemon->experience, min($this->pokemon->experience + $this->experience, $maxExperience));
    }
}
