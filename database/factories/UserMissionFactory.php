<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Enums\MissionKind;
use Shared\Models\Achievement;
use Shared\Models\User;
use Shared\Models\UserMission;
use Shared\ValueObjects\MissionReward;

/**
 * @extends Factory<UserMission>
 */
final class UserMissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'kind'            => MissionKind::DAILY,
            'achievement_id'  => null,
            'date'            => today(),
            'difficulty'      => fake()->randomElement(MissionDifficulty::cases()),
            'goal'            => MissionGoal::CATCH_POKEMON,
            'goal_parameters' => [],
            'target'          => fake()->numberBetween(5, 10),
            'progress'        => 0,
            'reward'          => new MissionReward(money: 1_000),
            'completed_at'    => null,
            'claimed_at'      => null,
        ];
    }

    public function forAchievement(?Achievement $achievement = null): static
    {
        return $this->state(function () use ($achievement): array {
            $achievement ??= Achievement::factory()->create();

            return [
                'kind'            => MissionKind::ACHIEVEMENT,
                'achievement_id'  => $achievement->id,
                'date'            => null,
                'difficulty'      => null,
                'goal'            => $achievement->goal,
                'goal_parameters' => $achievement->goal_parameters,
                'target'          => $achievement->target,
                'reward'          => $achievement->reward,
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'progress'     => $attributes['target'],
            'completed_at' => now(),
        ]);
    }

    public function claimed(): static
    {
        return $this->completed()->state([
            'claimed_at' => now(),
        ]);
    }
}
