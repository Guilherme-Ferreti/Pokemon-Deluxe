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
use Illuminate\Support\Collection;
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
 * @property Collection<int, PokemonType> $types
 * @property int $pokedex_number
 * @property int $pokedex_order
 * @property string $genus
 * @property string $text_entry
 * @property SpeciesSprites $sprites
 * @property SpeciesPortraits $portraits
 * @property bool $is_baby
 * @property bool $is_legendary
 * @property bool $is_mythical
 * @property bool $is_mega
 * @property bool $is_gmax
 * @property bool $is_ultra_beast
 * @property Generation $generation
 * @property int|null $catch_rate
 * @property PokemonGenderRate $gender_rate
 * @property bool $egg_obtainable
 * @property int $evolution_stage
 * @property int|null $wild_min_level
 * @property int|null $wild_max_level
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
            'types'           => AsEnumCollection::of(PokemonType::class),
            'pokedex_number'  => 'integer',
            'pokedex_order'   => 'integer',
            'generation'      => Generation::class,
            'is_baby'         => 'boolean',
            'is_legendary'    => 'boolean',
            'is_mythical'     => 'boolean',
            'is_mega'         => 'boolean',
            'is_gmax'         => 'boolean',
            'is_ultra_beast'  => 'boolean',
            'catch_rate'      => 'integer',
            'gender_rate'     => PokemonGenderRate::class,
            'egg_obtainable'  => 'boolean',
            'evolution_stage' => 'integer',
            'wild_min_level'  => 'integer',
            'wild_max_level'  => 'integer',
            'sprites'         => AsSpeciesSprites::class,
            'portraits'       => AsSpeciesPortraits::class,
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

    public function isFoundInTheWild(): bool
    {
        return ! is_null($this->wild_min_level);
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

    public function getDisplayPortrait(bool $isShiny = false): ?string
    {
        if ($isShiny) {
            return $this->portraits->shiny ?? $this->portraits->regular;
        }

        return $this->portraits->regular;
    }

    public function getDisplayFrontSprite(PokemonGender $gender = PokemonGender::MALE, bool $shiny = false): string
    {
        $sprite = null;

        if ($gender === PokemonGender::FEMALE && $shiny) {
            $sprite = $this->sprites->frontShinyFemale ?? $this->sprites->frontShiny;
        } elseif ($gender === PokemonGender::FEMALE) {
            $sprite = $this->sprites->frontFemale ?? $this->sprites->frontDefault;
        } elseif ($shiny) {
            $sprite = $this->sprites->frontShiny;
        } else {
            $sprite = $this->sprites->frontDefault;
        }

        return (string) $sprite;
    }

    public function getDisplayBackSprite(PokemonGender $gender = PokemonGender::MALE, bool $shiny = false): string
    {
        $sprite = null;

        if ($gender === PokemonGender::FEMALE && $shiny) {
            $sprite = $this->sprites->backShinyFemale ?? $this->sprites->backShiny;
        } elseif ($gender === PokemonGender::FEMALE) {
            $sprite = $this->sprites->backFemale ?? $this->sprites->backDefault;
        } elseif ($shiny) {
            $sprite = $this->sprites->backShiny;
        } else {
            $sprite = $this->sprites->backDefault;
        }

        return $sprite ?? $this->getDisplayFrontSprite($gender, $shiny);
    }

    public function getCatchRatePercentage(): float
    {
        $maxCatchRate = 255;

        return $this->catch_rate / $maxCatchRate * 100;
    }

    public function getDisplayCatchRatePercentage(): int|float
    {
        return round($this->getCatchRatePercentage(), 2) |> toIntIfWhole(...);
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
