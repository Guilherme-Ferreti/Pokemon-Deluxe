<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\ValueObjects\IncubatorIndex;

/**
 * @implements CastsAttributes<list<IncubatorIndex>, mixed>
 */
final class AsIncubatorIndexes implements CastsAttributes
{
    /**
     * @return list<IncubatorIndex>
     */
    public function get(Model $model, string $key, $value, array $attributes): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            return [];
        }

        return array_map(
            fn (int|string $index) => new IncubatorIndex((int) $index),
            $decoded,
        ) |> array_values(...);
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        throw_if(! is_array($value), InvalidArgumentException::class, "[{$key}] must be an array.");

        throw_if(! array_all($value, fn ($item) => $item instanceof IncubatorIndex), InvalidArgumentException::class, sprintf("All values of [{$key}] must be instances of [%s]", IncubatorIndex::class));

        return array_map(fn (IncubatorIndex $index) => $index->value, $value)
            |> array_values(...)
            |> json_encode(...)
            |> strval(...);
    }
}
