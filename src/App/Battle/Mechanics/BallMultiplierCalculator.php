<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use App\Battle\ValueObjects\CatchContext;
use Shared\Enums\Ailment;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Pokeball;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;

final class BallMultiplierCalculator
{
    private const string GUARANTEED_BALL_SLUG = 'master-ball';

    private const string MOON_BALL_EVOLUTION_ITEM_SLUG = 'moon-stone';

    private const float TIMER_BALL_TURN_BONUS = 0.3;

    private const float TIMER_BALL_MAX = 4;

    public function multiplier(Pokeball $pokeball, CatchContext $context): float
    {
        $conditionMultiplier = match ($pokeball->slug) {
            'quick-ball'  => $context->turn === 1 ? 5.0 : null,
            'timer-ball'  => $this->timerBallMultiplier($context->turn),
            'repeat-ball' => $context->playerOwnsSpecies ? 3.5 : null,
            'nest-ball'   => $this->nestBallMultiplier($context->wildLevel),
            'level-ball'  => $this->levelBallMultiplier($context->activeLevel, $context->wildLevel),
            'dusk-ball'   => $context->isNight || $context->area === Area::CAVE ? 3.0 : null,
            'dream-ball'  => $context->wildAilment === Ailment::SLEEP ? 4.0 : null,
            'love-ball'   => $this->isLoveBallMatch($context) ? 8.0 : null,
            'moon-ball'   => $this->evolvesWithMoonStone($context->wildSpecies) ? 4.0 : null,
            'net-ball'    => $this->hasAnyType($context->wildSpecies, [PokemonType::BUG, PokemonType::GRASS]) ? 3.5 : null,
            'lure-ball'   => $this->hasAnyType($context->wildSpecies, [PokemonType::WATER]) ? 3.5 : null,
            'dive-ball'   => $context->area === Area::COAST ? 3.5 : null,
            'heavy-ball'  => $this->hasAnyType($context->wildSpecies, [PokemonType::ROCK, PokemonType::STEEL, PokemonType::GROUND]) ? 3.5 : null,
            'fast-ball'   => $this->hasAnyType($context->wildSpecies, [PokemonType::ELECTRIC, PokemonType::FLYING]) ? 3.5 : null,
            'beast-ball'  => $context->wildSpecies->is_ultra_beast ? 5.0 : null,
            default       => null,
        };

        return $conditionMultiplier ?? $pokeball->catch_rate;
    }

    public function isGuaranteed(Pokeball $pokeball): bool
    {
        return $pokeball->slug === self::GUARANTEED_BALL_SLUG;
    }

    private function timerBallMultiplier(int $turn): float
    {
        return min(self::TIMER_BALL_MAX, round(1 + self::TIMER_BALL_TURN_BONUS * ($turn - 1), 1));
    }

    private function nestBallMultiplier(int $wildLevel): float
    {
        $untilLevel    = (int) config('game.catching.nest_ball_until_level');
        $maxMultiplier = (float) config('game.catching.nest_ball_max');

        return max(1, min($maxMultiplier, 1 + ($untilLevel - $wildLevel) / 9));
    }

    private function levelBallMultiplier(int $activeLevel, int $wildLevel): float
    {
        return match (true) {
            $activeLevel >= $wildLevel * 4 => 8.0,
            $activeLevel >= $wildLevel * 2 => 4.0,
            $activeLevel > $wildLevel      => 2.0,
            default                        => 1.0,
        };
    }

    private function isLoveBallMatch(CatchContext $context): bool
    {
        return $context->wildSpecies->is($context->activeSpecies)
            && $context->wildGender !== PokemonGender::GENDERLESS
            && $context->activeGender !== PokemonGender::GENDERLESS
            && $context->wildGender !== $context->activeGender;
    }

    private function evolvesWithMoonStone(Species $species): bool
    {
        return $species->evolutions->contains(
            fn (SpeciesEvolution $evolution): bool => $evolution->evolutionItem?->slug === self::MOON_BALL_EVOLUTION_ITEM_SLUG,
        );
    }

    /**
     * @param  list<PokemonType>  $types
     */
    private function hasAnyType(Species $species, array $types): bool
    {
        return $species->types->contains(fn (PokemonType $type): bool => in_array($type, $types, true));
    }
}
