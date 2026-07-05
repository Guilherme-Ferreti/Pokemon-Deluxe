<?php

declare(strict_types=1);

namespace Shared\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\ValueObjects\PokemonSprites;

/**
 * @property string $id
 * @property string $name
 * @property ElementalType $primary_type
 * @property ElementalType|null $secondary_type
 * @property int $national_dex_entry_number
 * @property string $genus
 * @property string $text_entry
 * @property PokemonSprites $sprites
 * @property bool $is_baby
 * @property bool $is_legendary
 * @property bool $is_mythical
 * @property bool $is_mega
 * @property bool $is_gmax
 * @property Generation $generation
 * @property int $capture_rate
 * @property string $internal_id
 * @property string $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, PokedexEvolution> $evolutions
 * @property-read Collection<int, PokedexEvolution> $previousEvolutions
 */
#[Table('pokedex')]
#[Unguarded]
final class Pokedex extends Model
{
    use HasUuids;

    #[Override]
    protected $casts = [
        'primary_type'              => ElementalType::class,
        'secondary_type'            => ElementalType::class,
        'national_dex_entry_number' => 'integer',
        'generation'                => Generation::class,
        'capture_rate'              => 'integer',
        'sprites'                   => PokemonSprites::class,
    ];

    /**
     * @return HasMany<PokedexEvolution, $this>
     */
    public function evolutions(): HasMany
    {
        return $this->hasMany(PokedexEvolution::class, 'from_pokedex_id');
    }

    /**
     * @return HasMany<PokedexEvolution, $this>
     */
    public function previousEvolutions(): HasMany
    {
        return $this->hasMany(PokedexEvolution::class, 'to_pokedex_id');
    }
}
