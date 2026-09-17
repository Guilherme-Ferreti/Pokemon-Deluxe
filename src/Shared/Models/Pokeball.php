<?php

declare(strict_types=1);

namespace Shared\Models;

use Database\Factories\PokeballFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;
use Shared\Enums\PokeballType;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string|null $sprite
 * @property PokeballType $type
 * @property float $capture_rate
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[WithoutTimestamps]
final class Pokeball extends Model
{
    /** @use HasFactory<PokeballFactory> */
    use HasFactory, HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'type'         => PokeballType::class,
            'capture_rate' => 'float',
        ];
    }
}
