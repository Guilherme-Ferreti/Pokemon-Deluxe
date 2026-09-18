<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\SpeciesLearnableMoveFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int|null $level_learn
 * @property bool $is_base_move
 * @property-read Species $species
 * @property-read Move $move
 */
#[WithoutTimestamps]
final class SpeciesLearnableMove extends Model
{
    /** @use HasFactory<SpeciesLearnableMoveFactory> */
    use HasFactory, HasUuids;

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

    public function isLearnedByLevelUp(): bool
    {
        return ! is_null($this->level_learn);
    }

    public function isLearnedByTm(): bool
    {
        return is_null($this->level_learn);
    }
}
