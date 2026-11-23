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
                'backDefault'      => null,
                'backFemale'       => null,
                'backShiny'        => null,
                'backShinyFemale'  => null,
                'frontDefault'     => null,
                'frontFemale'      => null,
                'frontShiny'       => null,
                'frontShinyFemale' => null,
            ]);
        }

        $sprites = array_map(fn ($item) => is_string($item) ? $item : null, $value);

        return new SpeciesSprites(...[
            'backDefault'      => $sprites['back_default'] ?? null,
            'backFemale'       => $sprites['back_female'] ?? null,
            'backShiny'        => $sprites['back_shiny'] ?? null,
            'backShinyFemale'  => $sprites['back_shiny_female'] ?? null,
            'frontDefault'     => $sprites['front_default'] ?? null,
            'frontFemale'      => $sprites['front_female'] ?? null,
            'frontShiny'       => $sprites['front_shiny'] ?? null,
            'frontShinyFemale' => $sprites['front_shiny_female'] ?? null,
        ]);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        throw_if(! $value instanceof SpeciesSprites, InvalidArgumentException::class, sprintf("[{$key}] must be an instance of [%s]", SpeciesSprites::class));

        return (string) json_encode([
            'back_default'       => $value->backDefault,
            'back_female'        => $value->backFemale,
            'back_shiny'         => $value->backShiny,
            'back_shiny_female'  => $value->backShinyFemale,
            'front_default'      => $value->frontDefault,
            'front_female'       => $value->frontFemale,
            'front_shiny'        => $value->frontShiny,
            'front_shiny_female' => $value->frontShinyFemale,
        ]);
    }
}
