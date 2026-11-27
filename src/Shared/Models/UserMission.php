<?php

declare(strict_types=1);

namespace Shared\Models;

use Carbon\CarbonImmutable;
use Database\Factories\UserMissionFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Shared\Casts\AsMissionReward;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Enums\MissionKind;
use Shared\ValueObjects\MissionReward;

/**
 * @property string $id
 * @property string $user_id
 * @property MissionKind $kind
 * @property string|null $achievement_id
 * @property CarbonImmutable|null $date
 * @property MissionDifficulty|null $difficulty
 * @property string $description
 * @property MissionGoal $goal
 * @property array<string, mixed> $goal_parameters
 * @property int $target
 * @property int $progress
 * @property MissionReward $reward
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $claimed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read Achievement|null $achievement
 */
#[Unguarded]
final class UserMission extends Model
{
    /** @use HasFactory<UserMissionFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'kind'            => MissionKind::class,
            'date'            => 'immutable_date',
            'difficulty'      => MissionDifficulty::class,
            'goal'            => MissionGoal::class,
            'goal_parameters' => 'array',
            'target'          => 'integer',
            'progress'        => 'integer',
            'reward'          => AsMissionReward::class,
            'completed_at'    => 'datetime',
            'claimed_at'      => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Achievement, $this>
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isClaimable(): bool
    {
        return $this->isCompleted() && $this->claimed_at === null;
    }
}
