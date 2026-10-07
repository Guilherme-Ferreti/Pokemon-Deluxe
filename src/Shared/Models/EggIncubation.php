<?php

declare(strict_types=1);

namespace Shared\Models;

use Carbon\CarbonImmutable;
use Database\Factories\EggIncubationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Shared\Enums\EggIncubationStatus;
use Shared\Enums\PokemonGender;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @property string $id
 * @property string $user_id
 * @property int $egg_incubator_index
 * @property string $species_id
 * @property PokemonGender $gender
 * @property bool $is_shiny
 * @property CarbonImmutable $ready_to_hatch_at
 * @property CarbonImmutable|null $hatched_at
 * @property CarbonImmutable|null $captured_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read Species $species
 */
#[TypeScript]
final class EggIncubation extends Model
{
    /** @use HasFactory<EggIncubationFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'egg_incubator_index' => 'integer',
            'is_shiny'            => 'boolean',
            'gender'              => PokemonGender::class,
            'ready_to_hatch_at'   => 'datetime',
            'hatched_at'          => 'datetime',
            'captured_at'         => 'datetime',
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
     * @return BelongsTo<Species, $this>
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('captured_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->whereNotNull('captured_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function incubating(Builder $query): void
    {
        $query
            ->active()
            ->whereFuture('ready_to_hatch_at')
            ->whereNull('hatched_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function readyToHatch(Builder $query): void
    {
        $query
            ->active()
            ->wherePast('ready_to_hatch_at')
            ->whereNull('hatched_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function readyToCapture(Builder $query): void
    {
        $query
            ->active()
            ->wherePast('ready_to_hatch_at')
            ->wherePast('hatched_at');
    }

    public function getStatus(): EggIncubationStatus
    {
        return EggIncubationStatus::fromModel($this);
    }
}
