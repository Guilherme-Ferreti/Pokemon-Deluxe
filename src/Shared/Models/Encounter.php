<?php

declare(strict_types=1);

namespace Shared\Models;

use App\Pokemon\ValueObjects\NewPokemon;
use Carbon\CarbonImmutable;
use Database\Factories\EncounterFactory;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;

/**
 * @property string $id
 * @property string $user_id
 * @property Area $area
 * @property string $species_id
 * @property int $level
 * @property bool $is_shiny
 * @property PokemonGender $gender
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read Species $species
 */
#[Unguarded]
final class Encounter extends Model
{
    /** @use HasFactory<EncounterFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'area'     => Area::class,
            'level'    => 'integer',
            'is_shiny' => 'boolean',
            'gender'   => PokemonGender::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Species, $this>
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function toNewPokemon(): NewPokemon
    {
        return new NewPokemon(
            species: $this->species,
            level: $this->level,
            is_shiny: $this->is_shiny,
            gender: $this->gender,
        );
    }
}
