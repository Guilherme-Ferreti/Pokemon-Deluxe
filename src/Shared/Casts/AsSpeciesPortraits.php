<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use Shared\ValueObjects\SpeciesPortraits;

/**
 * @implements CastsAttributes<SpeciesPortraits, mixed>
 */
final class AsSpeciesPortraits implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): SpeciesPortraits
    {
        if (! is_string($value) || ! is_array($value = json_decode($value, true))) {
            return new SpeciesPortraits(...[
                'regular' => null,
                'shiny'   => null,
            ]);
        }

        $sprites = array_map(fn ($item) => is_string($item) ? $item : null, $value);

        return new SpeciesPortraits(...[
            'regular' => $sprites['regular'] ?? null,
            'shiny'   => $sprites['shiny'] ?? null,
        ]);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (! $value instanceof SpeciesPortraits) {
            throw new InvalidArgumentException("$key must be an instance of " . SpeciesPortraits::class);
        }

        return (string) json_encode([
            'regular' => $value->regular,
            'shiny'   => $value->shiny,
        ]);
    }
}
