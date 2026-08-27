<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\SpeciesFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Override;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\ValueObjects\SpeciesPortraits;
use Shared\ValueObjects\SpeciesSprites;

/**
 * @property string $id
 * @property string $name
 * @property Collection<int, ElementalType> $types
 * @property int $pokedex_number
 * @property string $genus
 * @property string $text_entry
 * @property SpeciesSprites $sprites
 * @property SpeciesPortraits $portraits
 * @property bool $is_baby
 * @property bool $is_legendary
 * @property bool $is_mythical
 * @property bool $is_mega
 * @property bool $is_gmax
 * @property Generation $generation
 * @property int $capture_rate
 * @property string $slug
 * @property string $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read EloquentCollection<int, SpeciesEvolution> $evolutions
 * @property-read EloquentCollection<int, SpeciesEvolution> $previousEvolutions
 */
#[Table('species')]
#[Unguarded]
final class Species extends Model
{
    /** @use HasFactory<SpeciesFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'types'          => AsEnumCollection::of(ElementalType::class),
            'pokedex_number' => 'integer',
            'generation'     => Generation::class,
            'capture_rate'   => 'integer',
            'sprites'        => SpeciesSprites::class,
            'portraits'      => SpeciesPortraits::class,
        ];
    }

    /**
     * @return HasMany<SpeciesEvolution, $this>
     */
    public function evolutions(): HasMany
    {
        return $this->hasMany(SpeciesEvolution::class, 'from_species_id');
    }

    /**
     * @return HasMany<SpeciesEvolution, $this>
     */
    public function previousEvolutions(): HasMany
    {
        return $this->hasMany(SpeciesEvolution::class, 'to_species_id');
    }

    /**
     * @param  Builder<Species>  $query
     */
    #[Scope]
    protected function orderByPokedexNumber(Builder $query): void
    {
        $query->orderBy('pokedex_number')
            ->orderByRaw('
                CASE
                    WHEN is_mega = 0 AND is_gmax = 0 THEN 1
                    WHEN is_mega = 1 AND is_gmax = 0 THEN 2
                    WHEN is_mega = 0 AND is_gmax = 1 THEN 3
                END
            ')
            ->orderBy('id');
    }

    public function isSingleType(): bool
    {
        return count($this->types) === 1;
    }

    public function isDualType(): bool
    {
        return count($this->types) === 2;
    }
}
