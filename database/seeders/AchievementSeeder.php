<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Shared\Models\Achievement;
use Shared\ValueObjects\MissionReward;

final class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $path = __DIR__ . '/files/achievements.json';

        $achievements = json_decode((string) file_get_contents($path), true);

        foreach (array_values($achievements) as $index => $achievement) {
            Achievement::create([
                'slug'            => $achievement['id'],
                'name'            => $achievement['name'],
                'category'        => $achievement['category'],
                'goal'            => $achievement['goal'],
                'goal_parameters' => $achievement['goal_parameters'],
                'target'          => $achievement['target'],
                'reward'          => MissionReward::fromArray($achievement['reward']),
                'sort_order'      => $index + 1,
            ]);
        }
    }
}
