<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\SpeciesFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Shared\Casts\AsSpeciesPortraits;
use Shared\Casts\AsSpeciesSprites;
use Shared\Enums\Generation;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\SpeciesPortraits;
use Shared\ValueObjects\SpeciesSprites;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property array{0: PokemonType, 1?: PokemonType} $types
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
 * @property int|null $catch_rate
 * @property int $gender_rate
 * @property bool $egg_obtainable
 * @property-read EloquentCollection<int, SpeciesEvolution> $evolutions
 * @property-read EloquentCollection<int, SpeciesEvolution> $previousEvolutions
 * @property-read EloquentCollection<int, SpeciesLearnableMove> $learnableMoves
 * @property-read EloquentCollection<int, Pokemon> $pokemon
 */
#[Table('species')]
#[WithoutTimestamps]
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
            'types'          => AsEnumCollection::of(PokemonType::class),
            'pokedex_number' => 'integer',
            'generation'     => Generation::class,
            'is_baby'        => 'boolean',
            'is_legendary'   => 'boolean',
            'is_mythical'    => 'boolean',
            'is_mega'        => 'boolean',
            'is_gmax'        => 'boolean',
            'catch_rate'     => 'integer',
            'gender_rate'    => 'integer',
            'egg_obtainable' => 'boolean',
            'sprites'        => AsSpeciesSprites::class,
            'portraits'      => AsSpeciesPortraits::class,
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
        return $this->hasMany(SpeciesEvolution::class, 'into_species_id');
    }

    /**
     * @return HasMany<SpeciesLearnableMove, $this>
     */
    public function learnableMoves(): HasMany
    {
        return $this->hasMany(SpeciesLearnableMove::class);
    }

    /**
     * @return HasMany<Pokemon, $this>
     */
    public function pokemon(): HasMany
    {
        return $this->hasMany(Pokemon::class);
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

    public function isCapturable(): bool
    {
        return ! is_null($this->catch_rate);
    }

    public function isGenderless(): bool
    {
        return $this->gender_rate === -1;
    }

    public function getDisplayName(bool $isShiny = false): string
    {
        if ($isShiny) {
            return "Shiny {$this->name}";
        }

        return $this->name;
    }

    public function getDisplayFrontSprite(PokemonGender $gender = PokemonGender::MALE, bool $shiny = false): string
    {
        $sprite = null;

        if ($gender === PokemonGender::FEMALE && $shiny) {
            $sprite = $this->sprites->front_shiny_female ?? $this->sprites->front_shiny;
        } elseif ($gender === PokemonGender::FEMALE) {
            $sprite = $this->sprites->front_female ?? $this->sprites->front_default;
        } elseif ($shiny) {
            $sprite = $this->sprites->front_shiny;
        } else {
            $sprite = $this->sprites->front_default;
        }

        return (string) $sprite;
    }

    public function getCatchRatePercentage(): int|float
    {
        return toIntIfWhole(round(($this->catch_rate / 255) * 100, 2));
    }

    public function getMalePercentage(): int|float
    {
        if ($this->isGenderless()) {
            return 0;
        }

        return toIntIfWhole(round(((8 - $this->gender_rate) / 8) * 100, 2));
    }

    public function getFemalePercentage(): int|float
    {
        if ($this->isGenderless()) {
            return 0;
        }

        return toIntIfWhole(round(($this->gender_rate / 8) * 100, 2));
    }
}
