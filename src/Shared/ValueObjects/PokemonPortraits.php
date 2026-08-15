<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class PokemonPortraits implements Castable
{
    public function __construct(
        public ?string $normal,
        public ?string $shiny,
    ) {}

    /**
     * @param  array<string, mixed>  $arguments
     * @return CastsAttributes<PokemonPortraits, string>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, $value, array $attributes): PokemonPortraits
            {
                if (! is_string($value) || ! is_array($value = json_decode($value, true))) {
                    return new PokemonPortraits(...[
                        'normal' => null,
                        'shiny'  => null,
                    ]);
                }

                $sprites = array_map(fn ($item) => is_string($item) ? $item : null, $value);

                return new PokemonPortraits(...[
                    'normal' => $sprites['normal'],
                    'shiny'  => $sprites['shiny'],
                ]);
            }

            public function set(Model $model, string $key, $value, array $attributes): string
            {
                if (! $value instanceof PokemonPortraits) {
                    throw new InvalidArgumentException('The value must be an instance of PokemonPortraits.');
                }

                return (string) json_encode([
                    'normal' => $value->normal,
                    'shiny'  => $value->shiny,
                ]);
            }
        };
    }
}
