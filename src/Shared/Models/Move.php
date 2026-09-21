<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\MoveFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Enums\MoveCategory;
use Shared\Enums\MoveDamageClass;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property ElementalType $type
 * @property MoveCategory $category
 * @property MoveDamageClass $damage_class
 * @property int|null $power
 * @property int $accuracy
 * @property int $pp
 * @property Ailment|null $ailment
 * @property int|null $ailment_chance
 * @property int|null $flinch_chance
 * @property int|null $min_hits
 * @property int|null $max_hits
 * @property bool $is_z_move
 * @property bool $is_dynamax_move
 * @property Generation $generation
 * @property string $tm_code
 * @property-read EloquentCollection<int, SpeciesLearnableMove> $learnableBySpecies
 * @property-read EloquentCollection<int, Pokemon> $learnedByPokemon
 */
#[Table('moves')]
#[WithoutTimestamps]
final class Move extends Model
{
    /** @use HasFactory<MoveFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'type'            => ElementalType::class,
            'category'        => MoveCategory::class,
            'damage_class'    => MoveDamageClass::class,
            'power'           => 'integer',
            'accuracy'        => 'integer',
            'pp'              => 'integer',
            'ailment'         => Ailment::class,
            'ailment_chance'  => 'integer',
            'flinch_chance'   => 'integer',
            'min_hits'        => 'integer',
            'max_hits'        => 'integer',
            'is_z_move'       => 'boolean',
            'is_dynamax_move' => 'boolean',
            'generation'      => Generation::class,
        ];
    }

    /**
     * @return HasMany<SpeciesLearnableMove, $this>
     */
    public function learnableBySpecies(): HasMany
    {
        return $this->hasMany(SpeciesLearnableMove::class);
    }

    /**
     * @return BelongsToMany<Pokemon, $this>
     */
    public function learnedByPokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_moves');
    }
}
