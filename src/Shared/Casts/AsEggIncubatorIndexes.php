<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\ValueObjects\EggIncubatorIndex;

/**
 * @implements CastsAttributes<list<EggIncubatorIndex>, mixed>
 */
final class AsEggIncubatorIndexes implements CastsAttributes
{
    /**
     * @return list<EggIncubatorIndex>
     */
    public function get(Model $model, string $key, $value, array $attributes): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map(
            fn (int|string $index) => new EggIncubatorIndex((int) $index),
            $decoded,
        ));
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException("$key must be an array.");
        }

        if (! array_all($value, fn ($item) => $item instanceof EggIncubatorIndex)) {
            throw new InvalidArgumentException("All values of $key must be instances of " . EggIncubatorIndex::class);
        }

        $indexes = array_values(array_map(fn (EggIncubatorIndex $index) => $index->value, $value));

        return (string) json_encode($indexes);
    }
}
