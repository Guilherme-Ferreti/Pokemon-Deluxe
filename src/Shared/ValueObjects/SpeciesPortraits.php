<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class SpeciesPortraits implements Castable
{
    public function __construct(
        public ?string $regular,
        public ?string $shiny,
    ) {}

    /**
     * @param  array<string, mixed>  $arguments
     * @return CastsAttributes<SpeciesPortraits, string>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, $value, array $attributes): SpeciesPortraits
            {
                if (! is_string($value) || ! is_array($value = json_decode($value, true))) {
                    return new SpeciesPortraits(...[
                        'regular' => null,
                        'shiny'   => null,
                    ]);
                }

                $sprites = array_map(fn ($item) => is_string($item) ? $item : null, $value);

                return new SpeciesPortraits(...[
                    'regular' => $sprites['regular'],
                    'shiny'   => $sprites['shiny'],
                ]);
            }

            public function set(Model $model, string $key, $value, array $attributes): string
            {
                if (! $value instanceof SpeciesPortraits) {
                    throw new InvalidArgumentException('The value must be an instance of SpeciesPortraits.');
                }

                return (string) json_encode([
                    'regular' => $value->regular,
                    'shiny'   => $value->shiny,
                ]);
            }
        };
    }
}
