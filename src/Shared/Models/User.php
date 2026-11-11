<?php

declare(strict_types=1);

namespace Shared\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;
use Shared\Casts\AsIncubatorIndexes;
use Shared\ValueObjects\IncubatorIndex;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property list<IncubatorIndex> $purchased_incubator_indexes
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read EloquentCollection<int, Pokemon> $pokemon
 * @property-read EloquentCollection<int, Incubation> $incubations
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'password'                    => 'hashed',
            'purchased_incubator_indexes' => AsIncubatorIndexes::class,
        ];
    }

    /**
     * @return HasMany<Pokemon, $this>
     */
    public function pokemon(): HasMany
    {
        return $this->hasMany(Pokemon::class);
    }

    /**
     * @return HasMany<Incubation, $this>
     */
    public function incubations(): HasMany
    {
        return $this->hasMany(Incubation::class);
    }

    /**
     * @return HasMany<InventoryItem, $this>
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
