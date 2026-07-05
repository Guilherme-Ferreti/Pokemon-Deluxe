<?php

declare(strict_types=1);

namespace Shared\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property string $id
 * @property string $user_id
 * @property string $pokedex_id
 * @property int $level
 * @property int $experience
 * @property string[] $moves_ids
 * @property bool $in_party
 * @property bool $is_shiny
 * @property string $pokeball_id
 * @property Carbon $captured_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Pokedex $pokedex
 * @property-read Pokeball $pokeball
 */
#[Table('pokemon')]
#[Unguarded]
final class Pokemon extends Model
{
    use HasUuids;

    #[Override]
    protected $casts = [
        'level'       => 'integer',
        'experience'  => 'integer',
        'moves_ids'   => 'array',
        'in_party'    => 'boolean',
        'is_shiny'    => 'boolean',
        'captured_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Pokedex, $this>
     */
    public function pokedex(): BelongsTo
    {
        return $this->belongsTo(Pokedex::class);
    }

    /**
     * @return BelongsTo<Pokeball, $this>
     */
    public function pokeball(): BelongsTo
    {
        return $this->belongsTo(Pokeball::class);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function inParty(Builder $query): void
    {
        $query->where('in_party', true);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function inBox(Builder $query): void
    {
        $query->where('in_party', false);
    }

    public function getName(): string
    {
        if ($this->is_shiny) {
            return 'Shiny ' . $this->pokedex->name;
        }

        return $this->pokedex->name;
    }

    public function getFrontSprite(): string
    {
        if ($this->is_shiny) {
            return $this->pokedex->sprites->front_shiny ?? '';
        }

        return $this->pokedex->sprites->front_default ?? '';
    }
}
