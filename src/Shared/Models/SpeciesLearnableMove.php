<?php

declare(strict_types=1);

namespace Shared\Models;

use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read Species $species
 * @property-read Move $moves
 */
#[WithoutTimestamps]
final class SpeciesLearnableMove extends Model
{
    use HasUuids;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'level_learn'  => 'integer',
            'is_base_move' => 'boolean',
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
     * @return BelongsTo<Move, $this>
     */
    public function move(): BelongsTo
    {
        return $this->belongsTo(Move::class);
    }
}
