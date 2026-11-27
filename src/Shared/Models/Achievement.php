<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;
use Shared\Casts\AsMissionReward;
use Shared\Enums\MissionCategory;
use Shared\Enums\MissionGoal;
use Shared\ValueObjects\MissionReward;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property MissionCategory $category
 * @property MissionGoal $goal
 * @property array<string, mixed> $goal_parameters
 * @property int $target
 * @property MissionReward $reward
 * @property int $sort_order
 */
#[Unguarded]
#[WithoutTimestamps]
final class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'category'        => MissionCategory::class,
            'goal'            => MissionGoal::class,
            'goal_parameters' => 'array',
            'target'          => 'integer',
            'reward'          => AsMissionReward::class,
            'sort_order'      => 'integer',
        ];
    }
}
