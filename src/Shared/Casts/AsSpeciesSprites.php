<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use Shared\ValueObjects\SpeciesSprites;

/**
 * @implements CastsAttributes<SpeciesSprites, mixed>
 */
final class AsSpeciesSprites implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): SpeciesSprites
    {
        if (! is_string($value) || ! is_array($value = json_decode($value, true))) {
            return new SpeciesSprites(...[
                'back_default'       => null,
                'back_female'        => null,
                'back_shiny'         => null,
                'back_shiny_female'  => null,
                'front_default'      => null,
                'front_female'       => null,
                'front_shiny'        => null,
                'front_shiny_female' => null,
            ]);
        }

        $sprites = array_map(fn ($item) => is_string($item) ? $item : null, $value);

        return new SpeciesSprites(...[
            'back_default'       => $sprites['back_default'] ?? null,
            'back_female'        => $sprites['back_female'] ?? null,
            'back_shiny'         => $sprites['back_shiny'] ?? null,
            'back_shiny_female'  => $sprites['back_shiny_female'] ?? null,
            'front_default'      => $sprites['front_default'] ?? null,
            'front_female'       => $sprites['front_female'] ?? null,
            'front_shiny'        => $sprites['front_shiny'] ?? null,
            'front_shiny_female' => $sprites['front_shiny_female'] ?? null,
        ]);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        throw_if(! $value instanceof SpeciesSprites, InvalidArgumentException::class, sprintf("[{$key}] must be an instance of [%s]", SpeciesSprites::class));

        return (string) json_encode([
            'back_default'       => $value->back_default,
            'back_female'        => $value->back_female,
            'back_shiny'         => $value->back_shiny,
            'back_shiny_female'  => $value->back_shiny_female,
            'front_default'      => $value->front_default,
            'front_female'       => $value->front_female,
            'front_shiny'        => $value->front_shiny,
            'front_shiny_female' => $value->front_shiny_female,
        ]);
    }
}
