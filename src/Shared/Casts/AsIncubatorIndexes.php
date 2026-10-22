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

        return array_values(array_map(
            fn (int|string $index) => new IncubatorIndex((int) $index),
            $decoded,
        ));
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException("$key must be an array.");
        }

        if (! array_all($value, fn ($item) => $item instanceof IncubatorIndex)) {
            throw new InvalidArgumentException("All values of $key must be instances of " . IncubatorIndex::class);
        }

        $indexes = array_values(array_map(fn (IncubatorIndex $index) => $index->value, $value));

        return (string) json_encode($indexes);
    }
}
