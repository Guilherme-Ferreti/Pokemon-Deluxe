<?php

declare(strict_types=1);

namespace Shared\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BattleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Shared\Casts\AsBattleState;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\ValueObjects\BattleState;

/**
 * @property string $id
 * @property string $user_id
 * @property BattleType $type
 * @property BattleStatus $status
 * @property BattleState $state
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 */
#[Unguarded]
final class Battle extends Model
{
    /** @use HasFactory<BattleFactory> */
    use HasFactory, HasUuids, MassPrunable;

    #[Override]
    protected function casts(): array
    {
        return [
            'type'   => BattleType::class,
            'status' => BattleStatus::class,
            'state'  => AsBattleState::class,
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
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return self::query()
            ->whereNot('status', BattleStatus::ONGOING)
            ->where('updated_at', '<=', now()->subDays((int) config('game.battle.keep_ended_days')));
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function ongoing(Builder $query): void
    {
        $query->where('status', BattleStatus::ONGOING);
    }
}
