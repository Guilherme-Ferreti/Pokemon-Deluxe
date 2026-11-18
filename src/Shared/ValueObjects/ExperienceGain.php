<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-type ExperienceGainArray array{combatant_index: int, earned_experience: int, level_before: int, level_after: int, is_from_experience_share: bool}
 */
final readonly class ExperienceGain implements ValueObject
{
    public function __construct(
        public int $combatant_index,
        public int $earned_experience,
        public int $level_before,
        public int $level_after,
        public bool $is_from_experience_share,
    ) {
        $maxLevel = (int) config('game.pokemon.max_level');

        throw_if($combatant_index < 0, InvalidArgumentException::class, "The combatant index cannot be negative, [{$combatant_index}] given.");

        throw_if($earned_experience < 0, InvalidArgumentException::class, "The earned experience cannot be negative, [{$earned_experience}] given.");

        throw_if($level_before < 1 || $level_before > $maxLevel, InvalidArgumentException::class, "The level before must be between 1 and {$maxLevel}, [{$level_before}] given.");

        throw_if($level_after < $level_before || $level_after > $maxLevel, InvalidArgumentException::class, "The level after must be between {$level_before} and {$maxLevel}, [{$level_after}] given.");
    }

    /**
     * @param  ExperienceGainArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            combatant_index: $data['combatant_index'],
            earned_experience: $data['earned_experience'],
            level_before: $data['level_before'],
            level_after: $data['level_after'],
            is_from_experience_share: $data['is_from_experience_share'],
        );
    }

    /**
     * @return ExperienceGainArray
     */
    public function toArray(): array
    {
        return [
            'combatant_index'          => $this->combatant_index,
            'earned_experience'        => $this->earned_experience,
            'level_before'             => $this->level_before,
            'level_after'              => $this->level_after,
            'is_from_experience_share' => $this->is_from_experience_share,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
