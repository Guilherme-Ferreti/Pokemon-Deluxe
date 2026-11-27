<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\MissionCategory;
use Shared\Enums\MissionGoal;
use Shared\Models\Achievement;
use Shared\ValueObjects\MissionReward;

/**
 * @extends Factory<Achievement>
 */
final class AchievementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->numberBetween(1, 100);

        return [
            'slug'            => fake()->unique()->slug(3),
            'name'            => "Catch {$target} Pokémon",
            'category'        => MissionCategory::CATCHING,
            'goal'            => MissionGoal::CATCH_POKEMON,
            'goal_parameters' => [],
            'target'          => $target,
            'reward'          => new MissionReward(money: fake()->numberBetween(1, 10) * 1_000),
            'sort_order'      => fake()->numberBetween(1, 100),
        ];
    }
}
