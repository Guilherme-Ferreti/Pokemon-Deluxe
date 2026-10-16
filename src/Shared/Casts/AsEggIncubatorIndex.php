<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\ValueObjects\EggIncubatorIndex;

/**
 * @implements CastsAttributes<EggIncubatorIndex, mixed>
 */
final class AsEggIncubatorIndex implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): EggIncubatorIndex
    {
        return new EggIncubatorIndex((int) $value);
    }

    public function set(Model $model, string $key, $value, array $attributes): int
    {
        if (! $value instanceof EggIncubatorIndex) {
            throw new InvalidArgumentException("$key must be an instance of " . EggIncubatorIndex::class);
        }

        return $value->value;
    }
}
