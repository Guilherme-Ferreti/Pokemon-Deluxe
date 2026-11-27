<?php

declare(strict_types=1);

namespace App\Missions\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Interfaces\ValueObject;
use Shared\ValueObjects\MissionReward;

final readonly class NewDailyMission implements ValueObject
{
    /**
     * @param  array<string, string|int>  $goalParameters
     */
    public function __construct(
        public MissionDifficulty $difficulty,
        public MissionGoal $goal,
        public array $goalParameters,
        public int $target,
        public string $description,
        public MissionReward $reward,
    ) {
        throw_if($target < 1, InvalidArgumentException::class, "The target must be at least 1, [{$target}] given.");
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->difficulty === $other->difficulty
            && $this->goal === $other->goal
            && $this->goalParameters === $other->goalParameters
            && $this->target === $other->target
            && $this->description === $other->description
            && $this->reward->equals($other->reward);
    }
}
