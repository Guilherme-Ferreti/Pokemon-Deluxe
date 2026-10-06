<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\EggIncubationProcessFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;
use Shared\Enums\PokemonGender;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @property string $id
 * @property int $egg_incubator_index
 * @property string $species_id
 * @property PokemonGender $gender
 * @property bool $is_shiny
 * @property Carbon $egg_ready_to_hatch_at
 * @property Carbon|null $egg_hatched_at
 * @property Carbon|null $egg_captured_at
 * @property string $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Species $species
 * @property-read User $user
 */
#[TypeScript]
final class EggIncubationProcess extends Model
{
    /** @use HasFactory<EggIncubationProcessFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'egg_incubator_index'   => 'integer',
            'is_shiny'              => 'boolean',
            'gender'                => PokemonGender::class,
            'egg_ready_to_hatch_at' => 'datetime',
            'egg_hatched_at'        => 'datetime',
            'egg_captured_at'       => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Species, $this>
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('egg_captured_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->whereNotNull('egg_captured_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function incubating(Builder $query): void
    {
        $query
            ->active()
            ->whereFuture('egg_ready_to_hatch_at')
            ->whereNull('egg_hatched_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function readyToHatch(Builder $query): void
    {
        $query
            ->active()
            ->wherePast('egg_ready_to_hatch_at')
            ->whereNull('egg_hatched_at');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function readyToCapture(Builder $query): void
    {
        $query
            ->active()
            ->wherePast('egg_ready_to_hatch_at')
            ->wherePast('egg_hatched_at');
    }

    public function getStatus(): EggIncubationProcessesStatus
    {
        return EggIncubationProcessesStatus::fromModel($this);
    }
}
