<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\SpeciesFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Shared\Casts\AsSpeciesPortraits;
use Shared\Casts\AsSpeciesSprites;
use Shared\Enums\Generation;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\ValueObjects\SpeciesPortraits;
use Shared\ValueObjects\SpeciesSprites;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property array{0: PokemonType, 1?: PokemonType} $types
 * @property int $pokedex_number
 * @property int $pokedex_order Position in the pokedex: by number, with regular forms before mega forms before gmax forms
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
 * @property PokemonGenderRate $gender_rate
 * @property bool $egg_obtainable
 * @property-read EloquentCollection<int, SpeciesEvolution> $evolutions
 * @property-read EloquentCollection<int, SpeciesEvolution> $previousEvolutions
 * @property-read EloquentCollection<int, Move> $baseMoves
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
            'pokedex_order'  => 'integer',
            'generation'     => Generation::class,
            'is_baby'        => 'boolean',
            'is_legendary'   => 'boolean',
            'is_mythical'    => 'boolean',
            'is_mega'        => 'boolean',
            'is_gmax'        => 'boolean',
            'catch_rate'     => 'integer',
            'gender_rate'    => PokemonGenderRate::class,
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
     * @return BelongsToMany<Move, $this>
     */
    public function baseMoves(): BelongsToMany
    {
        return $this->belongsToMany(Move::class, 'species_base_move');
    }

    /**
     * @return HasMany<Pokemon, $this>
     */
    public function pokemon(): HasMany
    {
        return $this->hasMany(Pokemon::class);
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
        return $this->gender_rate === PokemonGenderRate::GENDERLESS;
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

    public function getRandomGender(): PokemonGender
    {
        if ($this->isGenderless()) {
            return PokemonGender::GENDERLESS;
        }

        return RandomHelper::chance($this->gender_rate->malePercentage())
            ? PokemonGender::MALE
            : PokemonGender::FEMALE;
    }
}
