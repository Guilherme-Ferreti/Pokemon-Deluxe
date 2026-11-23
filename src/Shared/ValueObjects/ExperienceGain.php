<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-type ExperienceGainArray array{combatant_index: int, earned_experience: int, level_before: int, level_after: int, is_from_experience_share: bool, is_at_max_level: bool}
 */
final readonly class ExperienceGain implements ValueObject
{
    public function __construct(
        public int $combatantIndex,
        public int $earnedExperience,
        public int $levelBefore,
        public int $levelAfter,
        public bool $isFromExperienceShare,
        public bool $isAtMaxLevel,
    ) {
        $maxLevel = (int) config('game.pokemon.max_level');

        throw_if($combatantIndex < 0, InvalidArgumentException::class, "The combatant index cannot be negative, [{$combatantIndex}] given.");

        throw_if($earnedExperience < 0, InvalidArgumentException::class, "The earned experience cannot be negative, [{$earnedExperience}] given.");

        throw_if($levelBefore < 1 || $levelBefore > $maxLevel, InvalidArgumentException::class, "The level before must be between 1 and {$maxLevel}, [{$levelBefore}] given.");

        throw_if($levelAfter < $levelBefore || $levelAfter > $maxLevel, InvalidArgumentException::class, "The level after must be between {$levelBefore} and {$maxLevel}, [{$levelAfter}] given.");

        throw_if($isAtMaxLevel && $levelBefore !== $maxLevel, InvalidArgumentException::class, "Only a Pokémon at level {$maxLevel} can be at the max level, [{$levelBefore}] given.");
    }

    /**
     * @param  ExperienceGainArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            combatantIndex: $data['combatant_index'],
            earnedExperience: $data['earned_experience'],
            levelBefore: $data['level_before'],
            levelAfter: $data['level_after'],
            isFromExperienceShare: $data['is_from_experience_share'],
            isAtMaxLevel: $data['is_at_max_level'],
        );
    }

    /**
     * @return ExperienceGainArray
     */
    public function toArray(): array
    {
        return [
            'combatant_index'          => $this->combatantIndex,
            'earned_experience'        => $this->earnedExperience,
            'level_before'             => $this->levelBefore,
            'level_after'              => $this->levelAfter,
            'is_from_experience_share' => $this->isFromExperienceShare,
            'is_at_max_level'          => $this->isAtMaxLevel,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
