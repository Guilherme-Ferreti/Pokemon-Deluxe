<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class PokemonSprites implements Castable
{
    public function __construct(
        public ?string $back_default,
        public ?string $back_female,
        public ?string $back_shiny,
        public ?string $back_shiny_female,
        public ?string $front_default,
        public ?string $front_female,
        public ?string $front_shiny,
        public ?string $front_shiny_female,
    ) {}

    /**
     * @param  array<string, mixed>  $arguments
     * @return CastsAttributes<PokemonSprites, string>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, $value, array $attributes): PokemonSprites
            {
                if (! is_string($value) || ! is_array($value = json_decode($value, true))) {
                    return new PokemonSprites(...[
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

                $sprites = array_map(fn ($item): ?string => is_string($item) ? $item : null, $value);

                return new PokemonSprites(...[
                    'back_default'       => $sprites['back_default'],
                    'back_female'        => $sprites['back_female'],
                    'back_shiny'         => $sprites['back_shiny'],
                    'back_shiny_female'  => $sprites['back_shiny_female'],
                    'front_default'      => $sprites['front_default'],
                    'front_female'       => $sprites['front_female'],
                    'front_shiny'        => $sprites['front_shiny'],
                    'front_shiny_female' => $sprites['front_shiny_female'],
                ]);
            }

            public function set(Model $model, string $key, $value, array $attributes): string
            {
                if (! $value instanceof PokemonSprites) {
                    throw new InvalidArgumentException('The value must be an instance of PokemonSprites.');
                }

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
        };
    }
}
