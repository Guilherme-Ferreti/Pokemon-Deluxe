<?php

declare(strict_types=1);

namespace Shared\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;
use Shared\Enums\PokeballType;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string|null $sprite
 * @property PokeballType $type
 * @property float $capture_rate
 * @property string $internal_id
 * @property string $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Unguarded]
final class Pokeball extends Model
{
    use HasUuids;

    #[Override]
    protected function casts(): array
    {
        return [
            'type'         => PokeballType::class,
            'capture_rate' => 'float',
        ];
    }
}
