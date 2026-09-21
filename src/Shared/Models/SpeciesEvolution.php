<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\SpeciesEvolutionFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Shared\Enums\PokemonGender;

/**
 * @property string $id
 * @property string $from_species_id
 * @property string $into_species_id
 * @property string|null $evolution_item_id
 * @property int|null $min_level
 * @property PokemonGender|null $gender
 * @property-read Species $from
 * @property-read Species $into
 * @property-read EvolutionItem|null $evolutionItem
 */
#[WithoutTimestamps]
final class SpeciesEvolution extends Model
{
    /** @use HasFactory<SpeciesEvolutionFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'min_level' => 'integer',
            'gender'    => PokemonGender::class,
        ];
    }

    /**
     * @return BelongsTo<Species, $this>
     */
    public function from(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'from_species_id');
    }

    /**
     * @return BelongsTo<Species, $this>
     */
    public function into(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'into_species_id');
    }

    /**
     * @return BelongsTo<EvolutionItem, $this>
     */
    public function evolutionItem(): BelongsTo
    {
        return $this->belongsTo(EvolutionItem::class);
    }
}
