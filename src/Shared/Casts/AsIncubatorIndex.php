<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\ValueObjects\IncubatorIndex;

/**
 * @implements CastsAttributes<IncubatorIndex, mixed>
 */
final class AsIncubatorIndex implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): IncubatorIndex
    {
        return new IncubatorIndex((int) $value);
    }

    public function set(Model $model, string $key, $value, array $attributes): int
    {
        throw_if(! $value instanceof IncubatorIndex, InvalidArgumentException::class, sprintf("[{$key}] must be an instance of [%s].", IncubatorIndex::class));

        return $value->value;
    }
}
