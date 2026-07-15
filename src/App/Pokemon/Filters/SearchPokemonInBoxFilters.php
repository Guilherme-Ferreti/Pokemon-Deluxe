<?php

declare(strict_types=1);

namespace App\Pokemon\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\Generation;
use Shared\Enums\PokemonCharacteristic;

final class SearchPokemonInBoxFilters
{
    public function __construct(
        public ?string $search,

        /** @var Collection<int, PokemonCharacteristic> */
        public Collection $characteristics,

        /** @var Collection<int, Generation> */
        public Collection $generations,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $characteristics = $request
            ->collect('characteristics')
            ->map(fn (string $value) => PokemonCharacteristic::tryFrom($value))
            ->unique()
            ->filter()
            ->values();

        $generations = $request
            ->collect('generations')
            ->map(fn (string $value) => Generation::tryFrom((int) $value))
            ->unique()
            ->filter()
            ->values();

        return new self(
            search: $request->query('search'),
            characteristics: $characteristics,
            generations: $generations
        );
    }
}
