<?php

declare(strict_types=1);

namespace Shared\Models;

use App\Pokemon\Mechanics\StatsCalculator;
use Database\Factories\PokemonFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Override;
use Shared\Enums\PokemonGender;
use Shared\ValueObjects\PokemonStats;

/**
 * @property string $id
 * @property string $user_id
 * @property string $original_user_id
 * @property string $species_id
 * @property int $level
 * @property int $experience
 * @property int $boost
 * @property bool $in_party
 * @property bool $is_shiny
 * @property PokemonGender $gender
 * @property string $pokeball_id
 * @property Carbon $caught_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Species $species
 * @property-read Pokeball $pokeball
 * @property-read EloquentCollection<int, Move> $knownMoves
 * @property-read EloquentCollection<int, Move> $activeMoves
 */
#[Table('pokemon')]
#[Unguarded]
final class Pokemon extends Model
{
    /** @use HasFactory<PokemonFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'level'      => 'integer',
            'experience' => 'integer',
            'boost'      => 'integer',
            'in_party'   => 'boolean',
            'is_shiny'   => 'boolean',
            'gender'     => PokemonGender::class,
            'caught_at'  => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function originalUser(): BelongsTo
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

    /**
     * @return BelongsTo<Pokeball, $this>
     */
    public function pokeball(): BelongsTo
    {
        return $this->belongsTo(Pokeball::class);
    }

    /**
     * @return BelongsToMany<Move, $this>
     */
    public function knownMoves(): BelongsToMany
    {
        return $this->belongsToMany(Move::class, 'pokemon_move')->withPivot('slot');
    }

    /**
     * @return BelongsToMany<Move, $this>
     */
    public function activeMoves(): BelongsToMany
    {
        return $this->knownMoves()
            ->wherePivotNotNull('slot')
            ->orderByPivot('slot');
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function inParty(Builder $query): void
    {
        $query->where('in_party', true);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function inBox(Builder $query): void
    {
        $query->where('in_party', false);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function regular(Builder $query): void
    {
        $query->where('is_shiny', false);
    }

    /**
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function shiny(Builder $query): void
    {
        $query->where('is_shiny', true);
    }

    public function isGenderless(): bool
    {
        return $this->gender === PokemonGender::GENDERLESS;
    }

    public function isMale(): bool
    {
        return $this->gender === PokemonGender::MALE;
    }

    public function isFemale(): bool
    {
        return $this->gender === PokemonGender::FEMALE;
    }

    public function getDisplayName(): string
    {
        return $this->species->getDisplayName($this->is_shiny);
    }

    public function getDisplayFrontSprite(): string
    {
        return $this->species->getDisplayFrontSprite($this->gender, $this->is_shiny);
    }

    public function getStats(): PokemonStats
    {
        return new StatsCalculator()->calculate($this->level, $this->boost, $this->is_shiny);
    }
}
